const { WebSocketServer } = require('ws');
const http = require('http');

const PORT = 6001;

// Crear servidor HTTP para recibir notificaciones REST
const server = http.createServer((req, res) => {
    // Configurar cabeceras CORS
    res.setHeader('Access-Control-Allow-Origin', '*');
    res.setHeader('Access-Control-Allow-Methods', 'POST, GET, OPTIONS');
    res.setHeader('Access-Control-Allow-Headers', 'Content-Type');

    if (req.method === 'OPTIONS') {
        res.writeHead(204);
        res.end();
        return;
    }

    // Ruta /publish para recibir notificaciones HTTP (ej. de n8n o microservicios)
    if (req.method === 'POST' && req.url === '/publish') {
        let body = '';
        req.on('data', chunk => {
            body += chunk.toString();
        });
        req.on('end', () => {
            try {
                const payload = JSON.parse(body);
                const { event, data } = payload;
                
                if (!event) {
                    res.writeHead(400, { 'Content-Type': 'application/json' });
                    res.end(JSON.stringify({ error: 'Missing event name' }));
                    return;
                }

                console.log(`Broadcasting event: ${event}`);
                const messageString = JSON.stringify({ event, data: data || {} });

                // Retransmitir a todos los clientes WebSocket conectados
                let clientCount = 0;
                wss.clients.forEach(client => {
                    if (client.readyState === 1) { // 1 = OPEN
                        client.send(messageString);
                        clientCount++;
                    }
                });

                res.writeHead(200, { 'Content-Type': 'application/json' });
                res.end(JSON.stringify({ success: true, clients_notified: clientCount }));
            } catch (err) {
                res.writeHead(400, { 'Content-Type': 'application/json' });
                res.end(JSON.stringify({ error: 'Invalid JSON body' }));
            }
        });
    } else {
        res.writeHead(404, { 'Content-Type': 'application/json' });
        res.end(JSON.stringify({ error: 'Endpoint not found' }));
    }
});

// Adjuntar el servidor de WebSockets al servidor HTTP
const wss = new WebSocketServer({ server });

wss.on('connection', (ws) => {
    console.log('Cliente conectado a WebSocket');
    
    // Enviar mensaje de bienvenida
    ws.send(JSON.stringify({ 
        event: 'connected', 
        data: { message: 'Conexión en tiempo real establecida exitosamente.' } 
    }));

    ws.on('close', () => {
        console.log('Cliente desconectado');
    });
});

server.listen(PORT, '0.0.0.0', () => {
    console.log(`Servidor WebSocket + HTTP ejecutándose en el puerto ${PORT}`);
});
