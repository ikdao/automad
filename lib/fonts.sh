#!/bin/bash

# Install or update Inter font using NPM and moving the required files to fonts/inter.

npm install
npm update

if [[ ! -d "fonts/inter" ]]
then
	mkdir -p "fonts/inter"
fi

rsync node_modules/typeface-inter/*.txt node_modules/typeface-inter/*.md node_modules/typeface-inter/Inter*Web/Inter-*.var.woff2 fonts/inter