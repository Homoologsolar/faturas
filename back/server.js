// server.js

require('dotenv').config();
const express = require('express');
const cors = require('cors');
const { inserirNovoIntegrador } = require('./db/connection');

const app = express();
const PORT = process.env.SERVER_PORT || 3000;

// --- CONFIGURAÇÃO DE MIDDLEWARES ---

// 1. CORS: Deve vir primeiro.
app.use(cors());

// 2. JSON Parser: Essencial para ler o `req.body`.
app.use(express.json());

// --- ROTAS DA APLICAÇÃO ---

// ROTA DE TESTE (GET)
app.get('/', (req, res) => {
    res.send('API Node.js rodando e pronta para receber POSTs.');
});

// ROTA PRINCIPAL (POST) para inserir dados
app.post('/api/integrador', async (req, res) => {
    // Este log é a nossa prova. Ele DEVE aparecer no terminal do backend.
    console.log('-> Requisição POST recebida em /api/integrador com o corpo:', req.body); 
    
    const { nome_do_integrador, numero_de_contato } = req.body;
    
    if (!nome_do_integrador || !numero_de_contato) {
        return res.status(400).json({ 
            message: 'Nome e numero de contato são obrigatórios.' 
        });
    }

    try {
        const resultado = await inserirNovoIntegrador(nome_do_integrador, numero_de_contato);
        res.status(201).json(resultado); 
    } catch (error) {
        console.error('Erro ao inserir dados no DB:', error);
        res.status(500).json({ 
            message: 'Erro interno ao processar a requisição.', 
            details: error.message 
        });
    }
});

app.listen(PORT, () => {
    console.log(`Servidor Express rodando na porta ${PORT}`);
});