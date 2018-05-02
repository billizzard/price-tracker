#!/usr/bin/env bash

mkdir -p .idea/runConfigurations
rm -f .idea/runConfigurations/conf*.xml
cp phpstorm/*.xml .idea/runConfigurations/