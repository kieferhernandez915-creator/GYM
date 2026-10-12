const express = require('express');
const mysql = require('mysql2/promise');
const cors = require('cors');

const app = express();
app.use(express.json());
app.use(cors());

// --- CONFIGURACIÓN DE LA BASE DE DATOS MYSQL ---
const pool = mysql.createPool({
    host: 'localhost',
    user: 'root',
    password: 'tu_password',
    database: 'fitpro_db',
    waitForConnections: true,
    connectionLimit: 10,
    queueLimit: 0
});

// --- MOTOR DE CÁLCULOS PROFESIONALES ---
function calculateBodyMetrics({ weight, height, age, gender, bodyFat, activityLevel, goal }) {
    const heightMeters = height / 100;
    const bmi = (weight / (heightMeters * heightMeters)).toFixed(2);

    let fatMass = null;
    let leanMass = null;
    if (bodyFat) {
        fatMass = ((weight * bodyFat) / 100).toFixed(2);
        leanMass = (weight - fatMass).toFixed(2);
    }

    let bmr = (10 * weight) + (6.25 * height) - (5 * age);
    bmr += (gender === 'male') ? 5 : -161;

    const tdee = Math.round(bmr * activityLevel);

    let targetCalories = tdee;
    if (goal === 'definition') targetCalories -= 400;
    if (goal === 'hypertrophy') targetCalories += 400;

    const proteinGrams = Math.round(weight * 2.0);
    const fatsGrams = Math.round(weight * 0.9);
    const proteinKcal = proteinGrams * 4;
    const fatsKcal = fatsGrams * 9;
    const carbsKcal = Math.max(0, targetCalories - (proteinKcal + fatsKcal));
    const carbsGrams = Math.round(carbsKcal / 4);

    return {
        bmi: parseFloat(bmi),
        leanMassKg: leanMass ? parseFloat(leanMass) : null,
        fatMassKg: fatMass ? parseFloat(fatMass) : null,
        bmr: Math.round(bmr),
        tdee: tdee,
        targetCalories: targetCalories,
        macros: { proteinGrams, carbsGrams, fatsGrams }
    };
}

// --- ENDPOINTS DE LA API ---

// 1. Guardar y calcular métricas corporales en MySQL
app.post('/api/metrics', async (req, res) => {
    try {
        const { userId, weight, height, age, gender, bodyFat, activityLevel, goal } = req.body;
        
        const results = calculateBodyMetrics({
            weight: parseFloat(weight),
            height: parseFloat(height),
            age: parseInt(age),
            gender,
            bodyFat: bodyFat ? parseFloat(bodyFat) : null,
            activityLevel: parseFloat(activityLevel),
            goal
        });

        const query = `
            INSERT INTO body_metrics_history 
            (user_id, weight_kg, height_cm, body_fat_pct, bmi, lean_mass_kg, fat_mass_kg, tdee_kcal, target_kcal, goal) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        `;

        await pool.execute(query, [
            userId, weight, height, bodyFat || null, 
            results.bmi, results.leanMassKg, results.fatMassKg, 
            results.tdee, results.targetCalories, goal
        ]);

        res.status(200).json({ status: "success", data: results });
    } catch (error) {
        res.status(500).json({ error: error.message });
    }
});

// 2. Crear Rutina con Ejercicios en MySQL (Transacción)
app.post('/api/routines', async (req, res) => {
    const connection = await pool.getConnection();
    try {
        await connection.beginTransaction();

        const { userId, title, description, exercises } = req.body;

        // Insertar cabecera de la rutina
        const [routineResult] = await connection.execute(
            'INSERT INTO routines (user_id, title, description) VALUES (?, ?, ?)',
            [userId, title, description || '']
        );
        const routineId = routineResult.insertId;

        // Insertar cada ejercicio de la rutina
        for (const ex of exercises) {
            await connection.execute(
                'INSERT INTO routine_exercises (routine_id, exercise_name, sets, reps, rest_seconds) VALUES (?, ?, ?, ?, ?)',
                [routineId, ex.name, ex.sets, ex.reps, ex.restSeconds]
            );
        }

        await connection.commit();
        connection.release();

        res.status(201).json({ status: "success", message: "Rutina guardada exitosamente en MySQL." });
    } catch (error) {
        await connection.rollback();
        connection.release();
        res.status(500).json({ error: error.message });
    }
});

// 3. Obtener el historial del usuario
app.get('/api/history/:userId', async (req, res) => {
    try {
        const { userId } = req.params;
        const [rows] = await pool.execute(
            'SELECT * FROM body_metrics_history WHERE user_id = ? ORDER BY date DESC',
            [userId]
        );
        res.status(200).json({ status: "success", data: rows });
    } catch (error) {
        res.status(500).json({ error: error.message });
    }
});

const PORT = process.env.PORT || 3000;
app.listen(PORT, () => console.log(`Servidor MySQL ejecutándose en el puerto ${PORT}`));
