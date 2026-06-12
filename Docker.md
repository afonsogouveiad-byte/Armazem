# Xuleta bàsica per programadors (Docker Compose)

## Comandes útils amb `docker-compose`

- **Arrancar tots els serveis**:
  ```bash
  docker-compose up
  ```

- **Arrancar en segon pla**:
  ```bash
  docker-compose up -d
  ```

- **Arrancar un servei concret**:
  ```bash
  docker-compose up NOM_SERVEI
  ```

- **Arrancar un servei concret en segon pla**:
  ```bash
  docker-compose up -d NOM_SERVEI
  ```

- **Aturar i eliminar tots els serveis**:
  ```bash
  docker-compose down
  ```

- **Aturar només un servei**:
  ```bash
  docker-compose stop NOM_SERVEI
  ```

- **Reiniciar serveis** (desconnecta, reconstrueix i reconnecta):
  ```bash
  docker-compose down && docker-compose up -d --build
  ```

- **Veure els logs**:
  ```bash
  docker-compose logs -f
  ```

- **Veure els logs d'un servei concret**:
  ```bash
  docker-compose logs -f NOM_SERVEI
  ```

- **Accedir a un contenidor**:
  ```bash
  docker-compose exec NOM_SERVEI bash
  ```

- **Reconstruir sense cache**:
  ```bash
  docker-compose build --no-cache
  ```

## Comandes útius amb `docker`

- **Llistar contenidors en execució**:
  ```bash
  docker ps
  ```

- **Parar un contenidor concret**:
  ```bash
  docker stop NOM_CONTENIDOR
  ```

- **Eliminar un contenidor aturat**:
  ```bash
  docker rm NOM_CONTENIDOR
  ```

- **Netejar contenidors, imatges i volums no utilitzats**:
  ```bash
  docker system prune -a
  ```

## Consells ràpids

- Si canvies el `docker-compose.yml` o algun `Dockerfile`, millor fer:
  ```bash
  docker-compose up -d --build
  ```

- Mapeig de ports habitual:
  ```yaml
  ports:
    - "LOCAL:CONTENIDOR"
  ```

- Volums per persistir dades:
  ```yaml
  volumes:
    - ./dades-local:/var/lib/postgresql/data
  ```
