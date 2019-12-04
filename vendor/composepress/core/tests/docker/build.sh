#!/bin/bash

for version in $(find . ! -path . -type d); do
    pushd $version
    version=$(basename "$version")
    docker build -t registry.gitlab.com/composepress/core/ci:$version .
    docker push registry.gitlab.com/composepress/core/ci:$version
    popd
done