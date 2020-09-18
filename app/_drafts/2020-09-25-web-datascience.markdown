---
title:  "WebGUI Data Science Setup"
date:   2020-09-25 9:00:46 +0200
categories: Projects
tags: [Project Finish, Docker, Data Science]
toc: true
published: true
---
The creation of a data science work environment based on [Docker-Compose](https://docs.docker.com/compose/) and web browser access. 

Next month I will start to work on my [master degree in data science](https://h-da.de/studium/studienangebot/studiengaenge/naturwissenschaft-und-mathematik/data-science-msc/). For my bachelor thesis I invested into a EPYC based server with 256 GB RAM. I want to use this to run **[Jupyter](https://jupyter.org/)**, **[RStudio Server](https://rstudio.com/)** and **[Visual Studio Code](https://code.visualstudio.com/)**.

## Introduction

### Docker

[Docker](https://docs.docker.com/) is a container engine, which allows you to isolate processes and their enviroments into units, called containers. No virtualization takes place!

A Docker container is defined through the [Dockerfile](https://docs.docker.com/engine/reference/builder/), which builds containers from **images** using a [*layered* read-only filesystem](https://docs.docker.com/storage/storagedriver/). For example, if you base multiple images on the same Ubuntu version, Docker only needs to store this base filesystem **once**. Every step in the Dockerfile can be cached as a layer, allowing quicker builds of images.

When you destroy (*down*) a container all the data it created **is lost**, because it isn't included in the image. [**Volumes**](https://docs.docker.com/storage/volumes/) allow the **persistent storage** of data files across container creations (*up*) and destructions. Volumes can be for example named or [**bound**](https://docs.docker.com/storage/bind-mounts/) onto a the host file system.

### Docker-Compose

Docker-Compose is a container orchestration program that allows you to define and configure containers to provide services and connect them to each other. Those services are defined in a `docker-compose.yml` file.

To install Docker and and Docker-Compose follow [these](https://docs.docker.com/engine/install/) and [these](https://docs.docker.com/compose/install/) instructions.

## Docker Tutorial

When you installed successfully installed Docker you should be able to create your first container.

### Running an interactive container

We will create a Ubuntu container and run a Bash session in it, as suggested by the documentation:

```sh
sudo docker run -it ubuntu bash
```

The flags `-i` and `-t` are necessary for running a container which needs to have a open [STDIN](https://en.wikipedia.org/wiki/Standard_streams#Standard_input_(stdin)) and [TTY](https://unix.stackexchange.com/a/5443).  Ubuntu is the name of the image we want to use for our container and after that follows the command we want to run to start the container process.

While you are exploring the filesystem, you will see a complete Ubuntu directory tree seperate from your host system. A quick `ps aux` reveals that Bash is the process running as this container.

After you listed the containers currently running in another terminal you should see the Ubuntu container:

``` sh
sudo docker container ls
# or
sudo docker ps
```

```
CONTAINER ID        IMAGE               COMMAND             CREATED             STATUS              PORTS               NAMES
175e2afff02a        ubuntu              "bash"              8 minutes ago       Up 8 minutes                            tender_galois
```

The name `tender_galois` was automatically generated, you can use a custom flag with the `--name NAME` flag.

It is possible to list your current downloaded images using the following command:

``` sh
sudo docker images
```

``` txt
REPOSITORY          TAG                 IMAGE ID            CREATED             SIZE
ubuntu              latest              4e2eef94cd6b        10 days ago         73.9MB
hello-world         latest              bf756fb1ae65        8 months ago        13.3kB
```

When we exit the Bash session (just run exit or Ctrl+D, which sends a EOF to STDIN), the container will be stopped. When listing all containers with `sudo docker container ls --all` it will be still visibile.

``` txt
CONTAINER ID        IMAGE               COMMAND             CREATED             STATUS                      PORTS               NAMES
175e2afff02a        ubuntu              "bash"              18 minutes ago      Exited (0) 3 minutes ago                        tender_galois
```

To start the container and attach your terminals standard streams (`-a` for STDOUT/ERR and `-i` for STDIN) use the following command. You can either refer to the name or id. It is also ok to use partial ids.

``` sh
sudo docker start -ia 17
# or
sudo docker start -ia tender_galois
```

It is also possible to leave the container running while just detaching from it, the default sequence for this is Ctrl+p followed by Ctrl+q. You can reattach it using

The container can be destroyed with the `rm` subcommand.

``` sh
sudo docker rm tender_galois
```

### Running a web service container

In this section we will use Docker to run a web service from a custom container.

This will be our PHP web app, save it as "index.php" in a directory called `webapp`:

``` php
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
  "http://www.w3.org/TR/html4/strict.dtd">
<html>
  <head>
    <title>Hello from <?php echo gethostname()?></title>
  </head>
  <body>
    <?php
      echo "<p>Hello World</p>";
      $date = date('d-m-y h:i:s');
      echo "It is {$date}!";
    ?>
  </body>
</html>
```

This is a possible `Dockerfile`

``` dockerfile
# Base this image on the php with apache image
FROM php:7.2-apache
# Copy the app
COPY webapp/ /var/www/html/
```

``` tree
.
├── Dockerfile
└── webapp
    └── index.php
```

The image can be build with:

``` sh
sudo docker build -t tutorial/webapp .
```

A complete rebuild can be triggered with the `--no-cache` flag. To start this container detached we can use the following command:

``` sh
sudo docker run -d --name webappcontainer tutorial/webapp
```

We can get the IP adress of the container (Only accessible from the host machine) with this command:

``` sh
#https://stackoverflow.com/questions/17157721/how-to-get-a-docker-containers-ip-address-from-the-host
{% raw %}
sudo docker inspect -f '{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}' webappcontainer
{% endraw %}
```

You should be able to open the IP in your web browser or use `wget` on it. To use port forwarding from the host machine we can expose this port, while starting the container.  The format is `[hostip:]host_port:container_port`. The host ip is optional, without it, the container will answer all requests to this port. This redirection uses iptables and can [overwrite certain firewalls](https://www.techrepublic.com/article/how-to-fix-the-docker-and-ufw-security-flaw/) (e. g. ufw).  

``` sh
# Stop and remove the old container:
sudo docker stop webappcontainer && sudo docker rm webappcontainer
sudo docker run -d --name webappcontainer -p 127.0.0.1:8080:80 tutorial/webapp
```

Now `http://localhost:8080` should work as the web address.

### Docker Compose

We can define our web app in a `docker-compose.yml` file. An example follows:

``` yaml
version: "2"
services:
    webapp: # service name, not container name
        build: .
        ports:
            127.0.0.1:8080:80

```

Docker-Compose uses the name of the directory where the `docker-compose.yml` file is located as a prefix for naming containers, volumes and networks. The `up` subcommand creates and starts the containers specified in the file. To start the containers detached, we use the `-d` flag:

``` sh
sudo docker-compose up -d
```

With the docker-compose command we can interact with all containers defined in the `docker-compose.yml` file. Some examples:

``` sh
sudo docker-compose ps
sudo docker-compose logs
sudo docker-compose restart webapp
sudo docker-compose stop webapp
sudo docker-compose start webapp
```

The containers from the file can be destroyed with `sudo docker-compose down`. Instead of using the default network, which connects the docker containers together, it creates one for the containers in the compose file. The webapp would be reachable under the hostname "webapp" by other containers in the same compose file. Exposed ports are defined in the Dockerfile, they are always accessible to the connected containers, the `-p` flag and `ports:` config redirect container ports from the host!

## Simple setup

The following compose file defines the needed containers and uses the reverse proxy Traefik to communicate requests for the different paths to the containers.

``` yaml
version: "2"

services:
   juypter:
      build: "./jupyter"
      environment:
         - "JUPYTER_ENABLE_LAB=yes" # set to yes for Lab
      volumes:
         - "/datapool/baker/finesim/datascience_data:/home/jovyan/work"
      #command: "start-notebook.sh --NotebookApp.password='sha1:'" # Custom password
      labels:
         - "traefik.http.routers.jupyter.rule=PathPrefix(`/jupyter`)"
         - "traefik.http.routers.jupyter.middlewares=jupyter-stripprefix"
         - "traefik.http.middlewares.jupyter-stripprefix.stripprefix.prefixes=/jupyter"
         - "traefik.http.services.jupyter.loadbalancer.server.port=8888"
         - "traefik.enable=true"
         - "traefik.http.routers.jupyter.entrypoints=web"

   rstudio:
      image: "rocker/rstudio"
      environment:
         - "PASSWORD=paswordhere"
         - "GROUPID=100"
      volumes:
         - "/datapool/baker/finesim/datascience_data:/home/rstudio/work"
      labels:
         - "traefik.http.routers.rstudio.rule=PathPrefix(`/rstudio`)"
         - "traefik.http.routers.rstudio.middlewares=rstudio-stripprefix"
         - "traefik.http.middlewares.rstudio-stripprefix.stripprefix.prefixes=/rstudio"
         - "traefik.http.services.rstudio.loadbalancer.server.port=8787"
         - "traefik.enable=true"
         - "traefik.http.routers.rstudio.entrypoints=web"

   reverse-proxy:
      image: traefik:v2.2
      command:
         - "--providers.docker"
         - "--providers.docker.exposedbydefault=false"
         - "--entrypoints.web.address=:80"
      ports:
         - "80:80"
      volumes:
         - /var/run/docker.sock:/var/run/docker.sock

```

There are many different Jupyter images available, which provide different environments and languages. In a different blog post I will describe the creation of a custom one. The password for rstudio needs to be set in the environment variable section. You can find the Jupyter token in the logs (`sudo docker-compose logs `)



``` yaml
version: "2"

services:
   juypter:
      build: "./jupyter"
      environment:
         - "JUPYTER_ENABLE_LAB=yes"
      volumes:
         - "/datapool/baker/finesim/datascience_data:/home/jovyan/work"
      command: "start-notebook.sh --NotebookApp.password='sha1:9164ac1d1a84:5e3355cd9f08ce669db51ad9060db94918e96378'"
      labels:
         - "traefik.http.routers.jupyter.rule=Host(`jupyter.ljansen.net`)"
         - "traefik.http.services.jupyter.loadbalancer.server.port=8888"
         - "traefik.enable=true"
         - "traefik.http.routers.jupyter.entrypoints=websecure"
         - "traefik.http.routers.jupyter.tls.certresolver=resolver"

   rstudio:
      image: "rocker/rstudio"
      environment:
         - "PASSWORD=FExqFRANiw6Fx.iEy"
         - "GROUPID=100"
      volumes:
         - "/datapool/baker/finesim/datascience_data:/home/rstudio/work"
      labels:
         - "traefik.http.routers.rstudio.rule=Host(`rstudio.ljansen.net`)"
         - "traefik.http.services.rstudio.loadbalancer.server.port=8787"
         - "traefik.enable=true"
         - "traefik.http.routers.rstudio.entrypoints=websecure"
         - "traefik.http.routers.rstudio.tls.certresolver=resolver"

   reverse-proxy:
      image: traefik:v2.2
      command:
         - "--providers.docker"
         - "--providers.docker.exposedbydefault=false"
         - "--entrypoints.web.address=:80"
         - "--entrypoints.web.http.redirections.entryPoint.to=websecure"
         - "--entrypoints.web.http.redirections.entryPoint.scheme=https"
         - "--entrypoints.web.http.redirections.entrypoint.permanent=true"
         - "--entrypoints.websecure.address=:443"
         - "--certificatesresolvers.resolver.acme.httpchallenge=true"
         - "--certificatesresolvers.resolver.acme.httpchallenge.entrypoint=web"
         - "--certificatesresolvers.resolver.acme.email=mail@lukas-jansen.de"
         - "--certificatesresolvers.resolver.acme.storage=/letsencrypt/acme.json"
      ports:
         - "80:80"
         - "443:443"
      volumes:
         - /var/run/docker.sock:/var/run/docker.sock
         - "./letsencrypt:/letsencrypt"

```


## References
{% bibliography --cited %}
