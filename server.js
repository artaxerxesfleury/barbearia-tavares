require('dotenv').config();
const express = require('express');
const cors = require('cors');
const path = require('path');
const { GoogleGenAI } = require('@google/genai');

const app = express();
const port = process.env.PORT || 3000;

// Configurar o cliente Gemini com a chave secreta vinda do .env
const ai = new GoogleGenAI({ apiKey: process.env.GEMINI_API_KEY });

app.use(cors());
app.use(express.json());

// Servir arquivos estáticos da pasta atual (index.html, styles.css)
app.use(express.static(path.join(__dirname)));

async function analisarFormatos(formatoRosto) {
    const systemPrompt = `Você é um barbeiro especialista em visagismo facial.
O usuário possui formato de rosto: ${formatoRosto}.
Sua tarefa é sugerir o melhor "Corte" e "Barba" para harmonizar e disfarçar imperfeições desse formato.
Responda EXATAMENTE neste formato JSON, sem crases markdown:
{
  "corte": "Nome e explicação do corte",
  "barba": "Nome e explicação da barba"
}`;

    const response = await ai.models.generateContent({
        model: 'gemini-2.5-flash',
        contents: systemPrompt,
        config: {
           responseMimeType: "application/json"
        }
    });
    
    return JSON.parse(response.text);
}

// Endpoint para Visagismo
app.post('/api/gemini/visagismo', async (req, res) => {
    try {
        const { currentDetectedShape } = req.body;
        
        if (!currentDetectedShape) {
            return res.status(400).json({ error: "Formato de rosto não detectado ou não enviado." });
        }

        const analise = await analisarFormatos(currentDetectedShape);
        res.json({
             rosto: currentDetectedShape,
             corte: analise.corte,
             barba: analise.barba,
             matchPercentage: 95 + Math.floor(Math.random() * 5)
        });

    } catch (error) {
        console.error("Erro no Gemini:", error);
        res.status(500).json({ error: "Erro ao processar a inteligência artificial." });
    }
});

app.listen(port, () => {
    console.log(`✅ Servidor rodando em http://localhost:${port}`);
    console.log(`Pressione Ctrl+C para encerrar`);
});
