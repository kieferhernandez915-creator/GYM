<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FitPro Manager - Rutinas, Nutrición e Historial</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --bg-dark: #121212;
            --surface: #1e1e1e;
            --surface-card: #252525;
            --primary: #00e676;
            --primary-hover: #00c853;
            --accent: #00b0ff;
            --danger: #ff5252;
            --text-main: #e0e0e0;
            --text-muted: #9e9e9e;
            --border: #333333;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background: var(--bg-dark); color: var(--text-main); min-height: 100vh; display: flex; flex-direction: column; }

        header { background: var(--surface); border-bottom: 1px solid var(--border); padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
        header h1 { font-size: 1.5rem; color: var(--primary); display: flex; align-items: center; gap: 10px; }
        
        nav { display: flex; gap: 10px; }
        .tab-btn { background: transparent; border: 1px solid var(--border); color: var(--text-muted); padding: 8px 16px; border-radius: 6px; cursor: pointer; transition: all 0.3s; font-weight: 600; }
        .tab-btn.active, .tab-btn:hover { background: var(--primary); color: #000; border-color: var(--primary); }

        main { flex: 1; max-width: 1100px; width: 100%; margin: 20px auto; padding: 0 20px; }
        .tab-content { display: none; }
        .tab-content.active { display: block; animation: fadeIn 0.3s ease-in-out; }

        @keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }

        .card { background: var(--surface); border: 1px solid var(--border); border-radius: 12px; padding: 25px; margin-bottom: 20px; box-shadow: 0 4px 20px rgba(0,0,0,0.4); }
        h2 { color: var(--primary); margin-bottom: 20px; font-size: 1.25rem; border-bottom: 1px solid var(--border); padding-bottom: 10px; }

        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        @media (max-width: 768px) { .grid-2 { grid-template-columns: 1fr; } }

        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 6px; font-size: 0.9rem; color: var(--text-muted); font-weight: 500; }
        input, select, textarea { width: 100%; padding: 12px; border-radius: 8px; border: 1px solid var(--border); background: var(--surface-card); color: #fff; font-size: 1rem; }
        input:focus, select:focus { outline: none; border-color: var(--primary); }

        button { padding: 12px 20px; border-radius: 8px; border: none; font-weight: bold; cursor: pointer; transition: background 0.2s; font-size: 0.95rem; }
        .btn-primary { background: var(--primary); color: #000; width: 100%; }
        .btn-primary:hover { background: var(--primary-hover); }
        .btn-secondary { background: var(--accent); color: #000; }
        .btn-danger { background: var(--danger); color: #fff; padding: 6px 12px; font-size: 0.85rem; }
        .btn-outline { background: transparent; border: 1px solid var(--border); color: var(--text-main); }
        .btn-outline:hover { background: #333; }

        .exercise-row { display: flex; gap: 10px; margin-bottom: 12px; align-items: center; background: var(--surface-card); padding: 10px; border-radius: 8px; border: 1px solid var(--border); }
        .exercise-row input { flex: 2; }
        .exercise-row input.small { flex: 1; }

        .results-box { background: var(--surface-card); border-left: 4px solid var(--primary); padding: 15px; border-radius: 0 8px 8px 0; margin-top: 20px; }
        .results-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap: 15px; margin-top: 15px; }
        .metric-card { background: var(--surface); padding: 12px; border-radius: 6px; text-align: center; border: 1px solid var(--border); }
        .metric-card span { display: block; font-size: 0.8rem; color: var(--text-muted); margin-bottom: 5px; }
        .metric-card strong { font-size: 1.15rem; color: var(--primary); }

        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid var(--border); font-size: 0.95rem; }
        th { color: var(--text-muted); font-weight: 600; background: var(--surface-card); }

        .badge { display: inline-block; padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: bold; }
        .badge.definition { background: rgba(255, 82, 82, 0.2); color: #ff5252; }
        .badge.maintenance { background: rgba(0, 176, 255, 0.2); color: #00b0ff; }
        .badge.hypertrophy { background: rgba(0, 230, 118, 0.2); color: #00e676; }
    </style>
</head>
<body>

<header>
    <h1>⚡ FitPro Manager</h1>
    <nav>
        <button class="tab-btn active" onclick="switchTab('metrics')">Composición & Macros</button>
        <button class="tab-btn" onclick="switchTab('routines')">Rutinas Pro</button>
        <button class="tab-btn" onclick="switchTab('nutrition')">Plan Alimentario</button>
        <button class="tab-btn" onclick="switchTab('history')">Historial & Gráfica</button>
    </nav>
</header>

<main>
    <!-- MÓDULO 1: COMPOSICIÓN Y CÁLCULOS -->
    <section id="metrics" class="tab-content active">
        <div class="grid-2">
            <div class="card">
                <h2>Calculadora Corporal & Calorías</h2>
                <div class="form-group">
                    <label>Peso Actual (kg)</label>
                    <input type="number" id="m-weight" value="78" step="0.1">
                </div>
                <div class="form-group">
                    <label>Altura (cm)</label>
                    <input type="number" id="m-height" value="175">
                </div>
                <div class="form-group">
                    <label>Edad</label>
                    <input type="number" id="m-age" value="20">
                </div>
                <div class="grid-2" style="gap: 10px;">
                    <div class="form-group">
                        <label>Sexo</label>
                        <select id="m-gender">
                            <option value="male">Masculino</option>
                            <option value="female">Femenino</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>% Grasa Corporal (Aprox.)</label>
                        <input type="number" id="m-fat" value="15" step="0.1">
                    </div>
                </div>
                <div class="form-group">
                    <label>Nivel de Actividad (Factor)</label>
                    <select id="m-activity">
                        <option value="1.2">Sedentario (poco o nada ejercicio)</option>
                        <option value="1.375">Ligero (ejercicio 1-3 días/sem)</option>
                        <option value="1.55" selected>Moderado (ejercicio 3-5 días/sem)</option>
                        <option value="1.725">Intenso (ejercicio 6-7 días/sem)</option>
                        <option value="1.9">Atleta Profesional</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Objetivo Principal</label>
                    <select id="m-goal">
                        <option value="definition">Definición (-400 kcal)</option>
                        <option value="maintenance">Mantenimiento (TDEE)</option>
                        <option value="hypertrophy" selected>Volumen / Hipertrofia (+400 kcal)</option>
                    </select>
                </div>
                <button class="btn-primary" onclick="calculateAndSaveMetrics()">Calcular y Registrar en Historial</button>
            </div>

            <div class="card">
                <h2>Resultados Actuales</h2>
                <div class="results-box">
                    <p style="font-weight: 600; color: var(--primary);">Resumen Biométrico</p>
                    <div class="results-grid">
                        <div class="metric-card"><span>IMC</span><strong id="res-bmi">--</strong></div>
                        <div class="metric-card"><span>Masa Magra</span><strong id="res-lean">-- kg</strong></div>
                        <div class="metric-card"><span>Masa Grasa</span><strong id="res-fat">-- kg</strong></div>
                        <div class="metric-card"><span>TMB (Basal)</span><strong id="res-bmr">-- kcal</strong></div>
                        <div class="metric-card"><span>Mantenimiento</span><strong id="res-tdee">-- kcal</strong></div>
                        <div class="metric-card"><span>Meta Calórica</span><strong id="res-target">-- kcal</strong></div>
                    </div>
                </div>

                <div class="results-box" style="border-left-color: var(--accent); margin-top: 15px;">
                    <p style="font-weight: 600; color: var(--accent);">Distribución de Macronutrientes</p>
                    <div class="results-grid">
                        <div class="metric-card"><span>Proteína (2g/kg)</span><strong id="macro-p">-- g</strong></div>
                        <div class="metric-card"><span>Grasas (0.9g/kg)</span><strong id="macro-f">-- g</strong></div>
                        <div class="metric-card"><span>Carbohidratos</span><strong id="macro-c">-- g</strong></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- MÓDULO 2: RUTINAS PROFESIONALES -->
    <section id="routines" class="tab-content">
        <div class="card">
            <h2>Constructor de Rutinas de Ejercicio</h2>
            <div class="form-group">
                <label>Título de la Rutina (Ej: Día 1 - Pecho y Tríceps)</label>
                <input type="text" id="routine-title" placeholder="Ej: Pecho Hipertrofia Avanzada">
            </div>

            <h3>Ejercicios de la Sesión</h3>
            <div id="exercises-container" style="margin: 15px 0;"></div>

            <div style="display: flex; gap: 10px; margin-bottom: 20px;">
                <button class="btn-outline" onclick="addExerciseRow()" style="flex: 1;">+ Agregar Ejercicio</button>
                <button class="btn-primary" onclick="saveRoutine()" style="flex: 1;">Guardar Rutina</button>
            </div>

            <h3>Mis Rutinas Guardadas</h3>
            <div id="saved-routines-list"></div>
        </div>
    </section>

    <!-- MÓDULO 3: PLAN ALIMENTARIO -->
    <section id="nutrition" class="tab-content">
        <div class="card">
            <h2>Planificador de Alimentos por Comida</h2>
            <p style="color: var(--text-muted); margin-bottom: 15px; font-size: 0.9rem;">Organiza tus comidas diarias en base a tu meta calórica calculada.</p>
            
            <div class="grid-2">
                <div class="form-group">
                    <label>Nombre de la Comida (Ej: Desayuno Pre-Entreno)</label>
                    <input type="text" id="meal-name" placeholder="Ej: Comida 1">
                </div>
                <div class="form-group">
                    <label>Alimentos e Ingredientes (Separados por coma)</label>
                    <input type="text" id="meal-foods" placeholder="Ej: 100g Avena, 200ml Claras, 1 Plátano">
                </div>
            </div>
            <button class="btn-primary" onclick="addMealToPlan()" style="margin-bottom: 20px;">Agregar Comida al Plan</button>

            <h3>Mi Menú Diario</h3>
            <table>
                <thead>
                    <tr>
                        <th>Comida</th>
                        <th>Alimentos</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody id="meals-table-body">
                    <tr><td colspan="3" style="text-align: center; color: var(--text-muted);">No hay comidas agregadas aún.</td></tr>
                </tbody>
            </table>
        </div>
    </section>

    <!-- MÓDULO 4: HISTORIAL Y GRÁFICA -->
    <section id="history" class="tab-content">
        <div class="card">
            <h2>Evolución Histórica de Peso y Grasa</h2>
            <div style="height: 300px; position: relative; margin-bottom: 20px;">
                <canvas id="historyChart"></canvas>
            </div>
            
            <h3>Registros Guardados</h3>
            <table>
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Peso (kg)</th>
                        <th>IMC</th>
                        <th>% Grasa</th>
                        <th>Meta Calórica</th>
                        <th>Objetivo</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody id="history-table-body">
                    <tr><td colspan="7" style="text-align: center; color: var(--text-muted);">Sin registros en el historial. Calcula tus métricas para guardar el primero.</td></tr>
                </tbody>
            </table>
        </div>
    </section>
</main>

<script>
    let state = {
        metricsHistory: JSON.parse(localStorage.getItem('fitpro_metrics')) || [],
        routines: JSON.parse(localStorage.getItem('fitpro_routines')) || [],
        meals: JSON.parse(localStorage.getItem('fitpro_meals')) || []
    };

    let historyChartInstance = null;

    function switchTab(tabId) {
        document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
        document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
        
        document.getElementById(tabId).classList.add('active');
        event.currentTarget.classList.add('active');

        if(tabId === 'history') {
            renderHistoryTable();
            renderHistoryChart();
        }
    }

    // --- CÁLCULOS Y MÉTRICAS ---
    function calculateAndSaveMetrics() {
        const weight = parseFloat(document.getElementById('m-weight').value);
        const height = parseFloat(document.getElementById('m-height').value);
        const age = parseInt(document.getElementById('m-age').value);
        const gender = document.getElementById('m-gender').value;
        const bodyFat = parseFloat(document.getElementById('m-fat').value);
        const activity = parseFloat(document.getElementById('m-activity').value);
        const goal = document.getElementById('m-goal').value;

        if(!weight || !height || !age) {
            alert('Por favor completa los datos biométricos principales.');
            return;
        }

        const heightM = height / 100;
        const bmi = (weight / (heightM * heightM)).toFixed(1);
        const fatMass = ((weight * bodyFat) / 100).toFixed(1);
        const leanMass = (weight - fatMass).toFixed(1);

        let bmr = (10 * weight) + (6.25 * height) - (5 * age);
        bmr += (gender === 'male') ? 5 : -161;
        bmr = Math.round(bmr);

        const tdee = Math.round(bmr * activity);
        let targetCalories = tdee;
        if(goal === 'definition') targetCalories -= 400;
        if(goal === 'hypertrophy') targetCalories += 400;

        const proteinG = Math.round(weight * 2.0);
        const fatsG = Math.round(weight * 0.9);
        const carbsG = Math.max(0, Math.round((targetCalories - (proteinG * 4 + fatsG * 9)) / 4));

        document.getElementById('res-bmi').innerText = bmi;
        document.getElementById('res-lean').innerText = leanMass + ' kg';
        document.getElementById('res-fat').innerText = fatMass + ' kg';
        document.getElementById('res-bmr').innerText = bmr + ' kcal';
        document.getElementById('res-tdee').innerText = tdee + ' kcal';
        document.getElementById('res-target').innerText = targetCalories + ' kcal';

        document.getElementById('macro-p').innerText = proteinG + ' g';
        document.getElementById('macro-f').innerText = fatsG + ' g';
        document.getElementById('macro-c').innerText = carbsG + ' g';

        const record = {
            date: new Date().toLocaleDateString(),
            weight,
            bmi,
            bodyFat,
            targetCalories,
            goal
        };

        state.metricsHistory.push(record);
        localStorage.setItem('fitpro_metrics', JSON.stringify(state.metricsHistory));
        renderHistoryTable();
        alert('¡Métricas calculadas y guardadas exitosamente en el historial!');
    }

    // --- RUTINAS ---
    function addExerciseRow() {
        const container = document.getElementById('exercises-container');
        const div = document.createElement('div');
        div.className = 'exercise-row';
        div.innerHTML = `
            <input type="text" placeholder="Nombre ejercicio (ej: Press de Banca)" class="ex-name" required>
            <input type="text" placeholder="Series (ej: 4)" class="ex-sets small" required>
            <input type="text" placeholder="Reps (ej: 8-10)" class="ex-reps small" required>
            <input type="text" placeholder="Descanso (ej: 90s)" class="ex-rest small" required>
            <button class="btn-danger" onclick="this.parentElement.remove()">X</button>
        `;
        container.appendChild(div);
    }

    function saveRoutine() {
        const title = document.getElementById('routine-title').value;
        const rows = document.querySelectorAll('.exercise-row');

        if(!title || rows.length === 0) {
            alert('Ingresa un título y al menos un ejercicio.');
            return;
        }

        const exercises = Array.from(rows).map(row => ({
            name: row.querySelector('.ex-name').value,
            sets: row.querySelector('.ex-sets').value,
            reps: row.querySelector('.ex-reps').value,
            rest: row.querySelector('.ex-rest').value
        }));

        state.routines.push({ title, exercises, date: new Date().toLocaleDateString() });
        localStorage.setItem('fitpro_routines', JSON.stringify(state.routines));
        
        document.getElementById('routine-title').value = '';
        document.getElementById('exercises-container').innerHTML = '';
        addExerciseRow();
        renderRoutinesList();
        alert('Rutina guardada correctamente.');
    }

    function renderRoutinesList() {
        const listContainer = document.getElementById('saved-routines-list');
        if(state.routines.length === 0) {
            listContainer.innerHTML = '<p style="color: var(--text-muted); font-size: 0.9rem;">No hay rutinas guardadas.</p>';
            return;
        }

        listContainer.innerHTML = state.routines.map((r, index) => `
            <div style="background: var(--surface-card); padding: 15px; border-radius: 8px; margin-top: 10px; border: 1px solid var(--border);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <strong>${r.title}</strong>
                    <span style="font-size: 0.8rem; color: var(--text-muted);">${r.date}</span>
                </div>
                <ul style="padding-left: 20px; font-size: 0.9rem; color: var(--text-muted);">
                    ${r.exercises.map(ex => `<li>${ex.name} — ${ex.sets} series x ${ex.reps} reps (Descanso: ${ex.rest})</li>`).join('')}
                </ul>
                <button class="btn-danger" style="margin-top: 10px;" onclick="deleteRoutine(${index})">Eliminar Rutina</button>
            </div>
        `).join('');
    }

    function deleteRoutine(index) {
        state.routines.splice(index, 1);
        localStorage.setItem('fitpro_routines', JSON.stringify(state.routines));
        renderRoutinesList();
    }

    // --- PLAN ALIMENTARIO ---
    function addMealToPlan() {
        const name = document.getElementById('meal-name').value;
        const foods = document.getElementById('meal-foods').value;

        if(!name || !foods) {
            alert('Completa el nombre de la comida y sus alimentos.');
            return;
        }

        state.meals.push({ name, foods });
        localStorage.setItem('fitpro_meals', JSON.stringify(state.meals));
        
        document.getElementById('meal-name').value = '';
        document.getElementById('meal-foods').value = '';
        renderMealsTable();
    }

    function renderMealsTable() {
        const tbody = document.getElementById('meals-table-body');
        if(state.meals.length === 0) {
            tbody.innerHTML = '<tr><td colspan="3" style="text-align: center; color: var(--text-muted);">No hay comidas agregadas aún.</td></tr>';
            return;
        }

        tbody.innerHTML = state.meals.map((m, index) => `
            <tr>
                <td><strong>${m.name}</strong></td>
                <td>${m.foods}</td>
                <td><button class="btn-danger" onclick="deleteMeal(${index})">Eliminar</button></td>
            </tr>
        `).join('');
    }

    function deleteMeal(index) {
        state.meals.splice(index, 1);
        localStorage.setItem('fitpro_meals', JSON.stringify(state.meals));
        renderMealsTable();
    }

    // --- HISTORIAL Y GRÁFICA CON ELIMINACIÓN ---
    function renderHistoryTable() {
        const tbody = document.getElementById('history-table-body');
        if(state.metricsHistory.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; color: var(--text-muted);">Sin registros en el historial.</td></tr>';
            return;
        }

        const goalLabels = { definition: 'Definición', maintenance: 'Mantenimiento', hypertrophy: 'Volumen' };

        tbody.innerHTML = state.metricsHistory.slice().reverse().map((h, originalIndex) => {
            const realIndex = state.metricsHistory.length - 1 - originalIndex;
            return `
                <tr>
                    <td>${h.date}</td>
                    <td><strong>${h.weight} kg</strong></td>
                    <td>${h.bmi}</td>
                    <td>${h.bodyFat}%</td>
                    <td>${h.targetCalories} kcal</td>
                    <td><span class="badge ${h.goal}">${goalLabels[h.goal]}</span></td>
                    <td><button class="btn-danger" onclick="deleteHistoryRecord(${realIndex})">Eliminar</button></td>
                </tr>
            `;
        }).join('');
    }

    function deleteHistoryRecord(index) {
        if(confirm('¿Deseas eliminar este registro del historial?')) {
            state.metricsHistory.splice(index, 1);
            localStorage.setItem('fitpro_metrics', JSON.stringify(state.metricsHistory));
            renderHistoryTable();
            renderHistoryChart();
        }
    }

    function renderHistoryChart() {
        const ctx = document.getElementById('historyChart').getContext('2d');
        if(historyChartInstance) historyChartInstance.destroy();

        const labels = state.metricsHistory.map(h => h.date);
        const weights = state.metricsHistory.map(h => h.weight);

        historyChartInstance = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels.length ? labels : ['Sin datos'],
                datasets: [{
                    label: 'Peso corporal (kg)',
                    data: weights.length ? weights : [0],
                    borderColor: '#00e676',
                    backgroundColor: 'rgba(0, 230, 118, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { labels: { color: '#e0e0e0' } } },
                scales: {
                    x: { ticks: { color: '#9e9e9e' }, grid: { color: '#333' } },
                    y: { ticks: { color: '#9e9e9e' }, grid: { color: '#333' } }
                }
            }
        });
    }

    window.onload = function() {
        addExerciseRow();
        renderRoutinesList();
        renderMealsTable();
        renderHistoryTable();
    };
</script>

</body>
</html>
