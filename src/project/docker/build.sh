#!/bin/bash

# ensure running bash
if ! [ -n "$BASH_VERSION" ];then
    echo "this is not bash, calling self with bash....";
    SCRIPT=$(readlink -f "$0")
    /bin/bash $SCRIPT
    exit;
fi

# Get the path to script just in case executed from elsewhere.
SCRIPT=$(readlink -f "$0")
SCRIPTPATH=$(dirname "$SCRIPT")
cd $SCRIPTPATH

# Load the variables from settings file.
source ../../settings/docker_settings.sh

cp -f Dockerfile ../../.
cp -f .dockerignore ../../.
cd ../../.

# Ask the user if they want to use the docker cache
read -p "Do you want to use a cached build (y/n)? " -n 1 -r
echo ""   # (optional) move to a new line
if [[ $REPLY =~ ^[Yy]$ ]]
then
    docker build --pull --tag $REGISTRY/$PROJECT_NAME .
else
    docker build --pull --no-cache --tag $REGISTRY/$PROJECT_NAME .
fi

# cleanup (remove duplicated docker files)
rm $SCRIPTPATH/../../Dockerfile
rm $SCRIPTPATH/../../.dockerignore

# Uncomment the line below if you have setup a registry and defined
# it within the docker_settings.sh file
docker push $REGISTRY/$PROJECT_NAME

echo "Run the container with either of the following command:"
echo "bash deploy.sh"
