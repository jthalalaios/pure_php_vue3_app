FROM node:22-alpine

WORKDIR /app

COPY vue3-fronted/package*.json ./
RUN npm install

COPY vue3-fronted/. .

EXPOSE 5173

CMD ["npm", "run", "dev"]
