FROM node:18-alpine AS build-stage

ARG API_BASE_URL=https://rr.buggregator.dev/api/v1
ENV VITE_API_BASE_URL=${API_BASE_URL}

WORKDIR /app

COPY package*.json ./
RUN npm install
COPY . .
RUN npm run build-only

FROM node:18-alpine AS production-stage

WORKDIR /app
RUN npm install -g serve
COPY --from=build-stage /app/dist /app/dist
EXPOSE 3000

CMD ["serve", "-s", "dist", "-l", "3000"]