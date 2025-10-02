// db/connection.js (Usando um Pool de Conexões)
require('dotenv').config(); 
const mysql = require('mysql2/promise'); // Importe a versão promise para async/await

// 1. Configuração do POOL de Conexões
// O Pool gerencia a abertura e o fechamento das conexões de forma eficiente.
const pool = mysql.createPool({
    host: process.env.DB_HOST,
    user: process.env.DB_USER,
    password: process.env.DB_PASSWORD,
    database: process.env.DB_NAME,
    port: process.env.DB_PORT,
    waitForConnections: true, // Espera se todas as conexões estiverem em uso
    connectionLimit: 10,       // Limite de conexões no Pool
    queueLimit: 0              // Fila infinita
});

// 2. Teste de Conexão com o Pool
async function testConnection() {
    try {
        await pool.getConnection(); // Tenta pegar uma conexão do Pool
        console.log('✅ Conexão MySQL/MariaDB estabelecida e Pool pronto.');
    } catch (err) {
        console.error('❌ ERRO CRÍTICO: Não foi possível conectar ao MySQL da Hostinger. Verifique as credenciais e o Acesso Remoto.', err.stack);
        // Em um projeto real, você encerraria o processo aqui: process.exit(1);
    }
}
testConnection();

/**
 * Insere um novo registro na sua tabela.
 */
async function inserirNovoIntegrador(nomeIntegrador, contato) {
    // Usamos o pool para executar a query diretamente (sem a necessidade de connection.promise().query)
    const sql = 'INSERT INTO integradores (nome_do_integrador, numero_de_contato) VALUES (?, ?)'; 
    
    // O pool.execute é otimizado e seguro contra SQL Injection
    const [results] = await pool.execute(sql, [nomeIntegrador, contato]);
    
    return {
        id: results.insertId,
        message: "Registro Integrador inserido com sucesso!",
    };
}

module.exports = {
    inserirNovoIntegrador 
};