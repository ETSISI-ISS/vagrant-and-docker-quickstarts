
# Assignment 1: Vagrant and Docker (course 2021-22)

![bg right:65% 100%](https://cdn.educba.com/academy/wp-content/uploads/2020/02/Vagrant-vs-Docker.jpg)

---

## Requirements summary

- Install [Virtual Box](https://www.virtualbox.org) 
- Install [Vagrant](https://www.vagrantup.com)
    - for Windows 
        - **disable** Hyper-V
    - for Mac
    - for Linux
- Install [Docker Desktop](https://www.docker.com/products/docker-desktop) 
    - for Windows 
        - **enable** WSL 2 (Recommended)
        - or enable Hyper-V
    - for Mac
- Install [Docker Engine](https://docs.docker.com/engine/install/) for Linux
- Or use [Docker Playground](https://labs.play-with-docker.com) if you want to avoid *Windows world* (Hyper-V, WSL, etc...)
- Register in [Docker Hub](https://hub.docker.com/signup)

- Account in DockerHub is required!

> All needed files can be download from this repository

---

# Vagrant assignment


- Initialize a directory for usage with Vagrant (project directory)
```sh
vagrant init
```

- Store the box hashicorp/bionic64
```sh
vagrant box add hashicorp/bionic64
```

- List your boxes
```sh
vagrant box list
```
---

- Edit the `vagrant` file

```sh
Vagrant.configure("2") do |config|
  config.vm.box = " hashicorp/bionic64"
end
```
---

- Start the virtual machine
```sh
vagrant up
``` 

- SSH into guest machine

```sh
vagrant ssh
```
- In your Vagrant project directoy (local machine), create an HTML directory 
```sh
mkdir html
```
- Create a file called `index.html` in the new directory, and populate it with the content for the index page.  
```sh
<!DOCTYPE html>
<html>
  <body>
    <h1>Getting started with Vagrant!</h1>
  </body>
</html>
```
> By default, Vagrant shares your project directory (the one containing the Vagrantfile) to the `/vagrant` directory in your guest machine. So, check that the local content has been synchronized  (#ls /vagrant)

> Terminate the SSH session with `CTRL+D`, or by logging out. 
---
- Edit the `vagrant` file to provision an apache webserver

```sh
Vagrant.configure("2") do |config|
  config.vm.box = " hashicorp/bionic64"
  config.vm.provision :shell, path: "bootstrap.sh"
end
```

- Edit the `bootstrap.sh` file (create it if needed)

```sh
#!/usr/bin/env bash
apt-get update
apt-get install -y apache2
if ! [ -L /var/www ]; then
    rm -rf /var/www
    ln -fs /vagrant /var/www
fi
```
---

- Reload your  virtual machine
```sh
vagrant reload --provision
``` 

- SSH into guest machine

```sh
vagrant ssh
``` 

- Run the following command in the guest machine

```sh
vagrant@vagrant:~$ wget -qO- 127.0.0.1
````
---

- Modifiy the `Vagrantfile`

```sh
Vagrant.configure("2") do |config|
  config.vm.box = "hashicorp/precise64"
  config.vm.provision :shell, path: "bootstrap.sh"
  config.vm.network :forwarded_port, guest: 80, host: 4567
end
``` 
---

- Run a `vagrant reload` or `vagrant up`  so that these changes can take effect. 
- Once the machine is running again, load http://127.0.0.1:4567 in the browser of your host machine. 

> You should see a web page that is being served from the virtual machine that was automatically setup by Vagrant
---

# Docker assignment

## Docker Images

- Define a container with Dockerfile (file `Dockerfile`)

```docker
# Use an official Python runtime as a parent image
FROM python:2.7-slim

# Set the working directory to /app
WORKDIR /app

# Copy the current directory contents into the container at /app
ADD . /app

# Install any needed packages specified in requirements.txt
RUN pip install --trusted-host pypi.python.org -r requirements.txt
# Make port 80 available to the world outside this container
EXPOSE 80

# Define environment variable
ENV NAME <put here your name>

# Run app.py when the container launches
CMD ["python", "app.py"]
```

---

- Create the app (file `app.py`)

```python
from flask import Flask
from redis import Redis, RedisError 
import os
import socket
   # Connect to Redis
redis = Redis(host="redis", db=0, socket_connect_timeout=2, socket_timeout=2) 
app = Flask(__name__)
@app.route("/")
def hello():
  try:
    visits = redis.incr("counter")
  except RedisError:
    visits = "<i>cannot connect to Redis, counter disabled</i>"
  html = "<h3>Hello {name}!</h3>" \
      "<b>Hostname:</b> {hostname}<br/>" \    
      "<b>Visits:</b> {visits}"
  return html.format(name=os.getenv("NAME", "world"), hostname=socket.gethostname(), visits=visits)
if __name__ == "__main__": 
  app.run(host='0.0.0.0', port=80)
```
---

- Create the file `requirements.txt`

```txt
Flask
Redis
```

---

- Build the app = Create the Docker image
---
- Run the container
---
- Stop and Run again
---
- Share the image into Docker Hub 

Note that the tag must be in format `user/image:number`

---

- Push the image and run again

---

## Docker Compose Services on Swarm

- Define `docker-compose-service.yml` file

```yaml
version: "3"
services:
  web:
    # replace username/repo:tag with your name and image details
    image: user/image:number
    deploy:
      replicas: 5
      resources:
        limits:
          cpus: "0.1"
          memory: 50M
      restart_policy:
        condition: on-failure
    ports:
      - "80:80"
    networks:
      - webnet
networks:
  webnet:
```

---

- Run the app as a service on a swarm

To delete the stack `docker stack rm webserver`

To leave the swarm `docker swarm leave --force`

---
## Docker Compose Stacks on Swarm

- Define `docker-compose-stack.yml` file
```yaml
version: "3"
services:
  web:
    # replace username/repo:tag with your name and image details
    image: user/image:number
    deploy:
      replicas: 5
      restart_policy:
        condition: on-failure
      resources:
        limits:
          cpus: "0.1"
          memory: 50M
    ports:
      - "80:80"
    networks:
      - webnet
  visualizer:
    image: dockersamples/visualizer:stable
    ports:
      - "8090:8080"
    volumes:
      - "/var/run/docker.sock:/var/run/docker.sock"
    deploy:
      placement:
        constraints: [node.role == manager]
    networks:
      - webnet
  redis:
    image: redis:alpine
    command: ["redis-server", "--appendonly", "yes"]
    hostname: redis
    networks:
      - webnet
    volumes:
      - redis-data:/data
networks:
  webnet:
volumes:
  redis-data:
```

---

- Run the app as a stack on a swarm

To delete the stack `docker stack rm webapp`
To leave the swarm `docker swarm leave --force`

---
Credits for https://github.com/docker-training/
