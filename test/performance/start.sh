#!/usr/bin/env bash

TEST_PATH=/home/work/repos/fis-plus-smarty-plugin/test/performance
FISP_PATH=/home/work/lib/node_modules/fis-plus
cd ${TEST_PATH}
sh product_code_ready.sh
npm cache clean
npm update -g fis-plus
cd ${FISP_PATH}
npm install fis-preprocessor-inline
cd ${TEST_PATH}
sh release.sh
sleep 2s
cp result_template.html result.html
php -f changeall.class.php
