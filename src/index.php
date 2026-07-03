<?php
session_start();
$adminFile = 'db/admin.json';

if (!file_exists($adminFile)) {
    header('Location: setup.php');
    exit;
}

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Système Planification - Gantt JSON Engine</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/frappe-gantt/0.6.1/frappe-gantt.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/frappe-gantt/0.6.1/frappe-gantt.min.js"></script>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background-color: #f8fafc; color: #0f172a; margin: 0; padding: 20px; }
        .navbar { display: flex; justify-content: space-between; align-items: center; background: #1e293b; color: white; padding: 15px 24px; border-radius: 8px; margin-bottom: 24px; }
        .navbar h1 { margin: 0; font-size: 18px; font-weight: 600; letter-spacing: -0.5px; }
        .logout-btn { color: #f1f5f9; text-decoration: none; font-size: 14px; background: #334155; padding: 8px 16px; border-radius: 6px; transition: background 0.2s; }
        .logout-btn:hover { background: #475569; }
        
        .main-layout { display: block; }
        .chart-card { background: white; border: 1px solid #e2e8f0; border-radius: 8px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 24px; min-height: 200px; }
        
        .empty-state { display: flex; flex-direction: column; align-items: center; justify-content: center; height: 200px; color: #64748b; }
        .empty-state p { margin: 0 0 16px 0; font-size: 15px; }
        
        .panel-card { background: white; border: 1px solid #e2e8f0; border-radius: 8px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
        .panel-card h3 { margin-top: 0; margin-bottom: 20px; color: #1e293b; border-bottom: 2px solid #f1f5f9; padding-bottom: 10px; }
        
        .task-form { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px; align-items: flex-end; }
        .form-item { display: flex; flex-direction: column; }
        .form-item label { font-size: 13px; font-weight: 500; color: #475569; margin-bottom: 6px; }
        .form-item input, .form-item select { padding: 9px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 14px; background: #fff; }
        
        .btn-submit { background: #2563eb; color: white; border: none; padding: 10px 20px; border-radius: 6px; font-weight: 600; font-size: 14px; cursor: pointer; transition: background 0.2s; height: 38px; }
        .btn-submit:hover { background: #1d4ed8; }
        
        .tasks-list-table { width: 100%; border-collapse: collapse; margin-top: 24px; font-size: 14px; }
        .tasks-list-table th { background: #f8fafc; text-align: left; padding: 12px; color: #475569; border-bottom: 2px solid #e2e8f0; }
        .tasks-list-table td { padding: 12px; border-bottom: 1px solid #f1f5f9; color: #334155; }
        .btn-delete { background: #ef4444; color: white; border: none; padding: 4px 8px; border-radius: 4px; cursor: pointer; font-size: 12px; }
        .btn-delete:hover { background: #dc2626; }
    </style>
</head>
<body>

    <div class="navbar">
        <h1>Gantt Custom Engine — Espace d'administration</h1>
        <a href="logout.php" class="logout-btn">Déconnexion</a>
    </div>

    <div class="main-layout">
        <div class="chart-card">
            <div id="gantt-container-target">
                </div>
        </div>

        <div class="panel-card">
            <h3>Ajouter ou référencer une tâche</h3>
            <form id="add-task-form" class="task-form" onsubmit="handleTaskSubmit(event)">
                <div class="form-item">
                    <label for="task-id">Code Unique Identifiant</label>
                    <input type="text" id="task-id" placeholder="Ex: Tache-1" required>
                </div>
                <div class="form-item">
                    <label for="task-name">Désignation / Libellé</label>
                    <input type="text" id="task-name" placeholder="Ex: Terrassement de la structure" required>
                </div>
                <div class="form-item">
                    <label for="task-start">Date de Début</label>
                    <input type="date" id="task-start" required>
                </div>
                <div class="form-item">
                    <label for="task-end">Date de Fin</label>
                    <input type="date" id="task-end" required>
                </div>
                <div class="form-item">
                    <label for="task-dep">Dépendance (Optionnel)</label>
                    <select id="task-dep">
                        <option value="">Aucune liaison</option>
                    </select>
                </div>
                <button type="submit" class="btn-submit">Créer la tâche</button>
            </form>

            <table class="tasks-list-table" id="tasks-table" style="display: none;">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Début</th>
                        <th>Fin</th>
                        <th>Progression</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="tasks-table-body"></tbody>
            </table>
        </div>
    </div>

    <script>
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
                // Nettoyer les dépendances brisées suite à la suppression
                tasks = tasks.filter(t => t.id !== id).map(t => {
                    if (t.dependencies === id) t.dependencies = "";
                    return t;
                });
                saveStateToStorage();
            }
        }

        // Boot
        loadEngine();
    </script>
</body>
</html>
