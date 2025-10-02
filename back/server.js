// server.js

require('dotenv').config();
const express = require('express');
const cors = require('cors');
const { inserirNovoIntegrador } = require('./db/connection'); // Importa a função do DB

const app = express();
const PORT = process.env.SERVER_PORT || 3000;

app.use(cors({
    // Permite que qualquer origem (seu index.html local) acesse a API.
    // Em produção, você colocaria o domínio do seu site aqui (ex: origin: 'https://homologsolar.com').
    origin: '*', 
    methods: ['GET', 'POST', 'OPTIONS'], // Permite apenas esses métodos
}));

// Middleware: Permite que o Express leia o corpo de requisições JSON
app.use(express.json()); 

// ROTA DE TESTE (GET)
app.get('/', (req, res) => {
    res.send('API Node.js rodando e pronta para receber POSTs.');
});

// ROTA PRINCIPAL (POST) para inserir dados
app.post('/api/integrador', async (req, res) => {
    const { nome_do_integrador, numero_de_contato} = req.body;
    
    if (!nome_do_integrador || !numero_de_contato) {
        return res.status(400).json({ 
            message: 'Nome e email são obrigatórios no corpo da requisição.' 
        });
    }

    try {
        const resultado = await inserirNovoIntegrador(nome_do_integrador, numero_de_contato);
        
        // Retorna 201 Created com os dados inseridos
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
    console.log(`Servidor Express rodando em http://localhost:${PORT}`);
});