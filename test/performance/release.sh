#!/usr/bin/env bash

TEST_PATH=/home/work/repos/fis-plus-smarty-plugin/test/performance
cd ${TEST_PATH}

IKNOW_CODE_PATH=${TEST_PATH}/product_code/iknow
IKNOW_OUTPUT_PATH=${TEST_PATH}/product_output/iknow

#iknow
rm -rf ${IKNOW_OUTPUT_PATH}
cd ${IKNOW_CODE_PATH}
fisp release -d ${IKNOW_OUTPUT_PATH} --no-color

fisp server install pc --root ${IKNOW_OUTPUT_PATH}

cd ${TEST_PATH}
cp ../../*.*.php ${IKNOW_OUTPUT_PATH}/plugin

