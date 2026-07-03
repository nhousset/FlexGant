let tasks = [];
let ganttChart = null;

async function loadEngine() {
    try {
        const response = await fetch('api.php');
        tasks = await response.json();
        populateDependencyDropdown();
        renderGanttView();
        renderTableList();
    } catch (e) {
        console.error("Erreur de communication API lors de la lecture", e);
    }
}

async function saveStateToStorage() {
    try {
        await fetch('api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(tasks)
        });
        populateDependencyDropdown();
        renderGanttView();
        renderTableList();
    } catch (e) {
        console.error("Erreur de communication API lors de l'écriture", e);
    }
}

function renderGanttView() {
    const wrapper = document.getElementById('gantt-container-target');
    
    if (tasks.length === 0) {
        wrapper.innerHTML = `
            <div class="empty-state">
                <p>Le fichier de données 'db/tasks.json' est actuellement vide.</p>
            </div>`;
        ganttChart = null;
        return;
    }

    wrapper.innerHTML = `<svg id="gantt-canvas-svg"></svg>`;
    
    ganttChart = new Gantt("#gantt-canvas-svg", tasks, {
        header_height: 50,
        column_width: 30,
        step: 24,
        view_modes: ['Day', 'Week', 'Month'],
        bar_height: 22,
        bar_corner_radius: 4,
        arrow_curve: 5,
        padding: 18,
        view_mode: 'Day',
        language: 'fr',
        on_date_change: function(task, start, end) {
            const idx = tasks.findIndex(t => t.id === task.id);
            if (idx > -1) {
                tasks[idx].start = start.toISOString().split('T')[0];
                tasks[idx].end = end.toISOString().split('T')[0];
                saveStateToStorage();
            }
        },
        on_progress_change: function(task, progress) {
            const idx = tasks.findIndex(t => t.id === task.id);
            if (idx > -1) {
                tasks[idx].progress = progress;
                saveStateToStorage();
            }
        }
    });
}

function populateDependencyDropdown() {
    const dropdown = document.getElementById('task-dep');
    dropdown.innerHTML = '<option value="">Aucune liaison</option>';
    tasks.forEach(t => {
        dropdown.innerHTML += `<option value="${t.id}">${t.name} (${t.id})</option>`;
    });
}

function renderTableList() {
    const table = document.getElementById('tasks-table');
    const tbody = document.getElementById('tasks-table-body');
    
    if (tasks.length === 0) {
        table.style.display = 'none';
        return;
    }
    
    table.style.display = 'table';
    tbody.innerHTML = '';
    
    tasks.forEach(t => {
        tbody.innerHTML += `
            <tr>
                <td><strong>${t.id}</strong></td>
                <td>${t.name}</td>
                <td>${t.start}</td>
                <td>${t.end}</td>
                <td>${t.progress}%</td>
                <td><button class="btn-delete" onclick="deleteTask('${t.id}')">Supprimer</button></td>
            </tr>
        `;
    });
}

function handleTaskSubmit(e) {
    e.preventDefault();
    const id = document.getElementById('task-id').value.trim();
    const name = document.getElementById('task-name').value.trim();
    const start = document.getElementById('task-start').value;
    const end = document.getElementById('task-end').value;
    const dependencies = document.getElementById('task-dep').value;

    if (tasks.some(t => t.id === id)) {
        alert("Erreur : Cet ID de tâche existe déjà.");
        return;
    }

    const newTask = {
        id: id,
        name: name,
        start: start,
        end: end,
        progress: 0,
        dependencies: dependencies
    };

    tasks.push(newTask);
    saveStateToStorage();
    document.getElementById('add-task-form').reset();
}

function deleteTask(id) {
    if (confirm(`Confirmez-vous la suppression de la tâche : ${id} ?`)) {
        tasks = tasks.filter(t => t.id !== id).map(t => {
            if (t.dependencies === id) t.dependencies = "";
            return t;
        });
        saveStateToStorage();
    }
}

// Initialisation
document.addEventListener('DOMContentLoaded', loadEngine);
