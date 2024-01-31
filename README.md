## Magento2 plugin for Ledyer checkout

## Setup

**Prerequisites**
First you'll need a docker client, such as [Docker Desktop](https://www.docker.com/products/docker-desktop).

**Requirements**
- comment out the following line:
```
      - "./:/bitnami/magento/app/code/Ledyer/Payment"
```
from the docker-compose.yml file. First we need it to be set up without the Ledyer plugin otherwise we run into an error saying env.php is missing.
- from the project root, run `docker compose up`.
- now stop the containers with `docker compose stop`.
- uncomment the line from the docker-compose.yml file.
- start the containers again with `docker compose up`.
- Open a browser and go to [localhost:8182](http://localhost:8182)

**Tips**
If you want to remove all containers and volumes in order to start from scratch you can run the cleanup script with `sh cleanup.sh`.

