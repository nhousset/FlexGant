<?php require_once 'header.php'; ?>

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
                    <input type="text" id="task-name" placeholder="Ex: Terrassement" required>
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

    <script src="app.js?now=<?= $noCache ?>"></script>

</body>
</html>
