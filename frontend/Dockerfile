FROM node:18-alpine

WORKDIR /app

# Copiar definiciones de dependencias primero para optimizar la caché de capas de Docker
COPY package.json package-lock.json ./

# Instalar dependencias
RUN npm install

# Copiar todo el código fuente del frontend
COPY . .

EXPOSE 5173

# Iniciar Vite en modo desarrollo expuesto a todas las interfaces de red
CMD ["npm", "run", "dev", "--", "--host"]
