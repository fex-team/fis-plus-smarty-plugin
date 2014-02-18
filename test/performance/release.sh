#!/usr/bin/env bash

TEST_PATH=/home/work/repos/fis-plus-smarty-plugin/test/performance
cd ${TEST_PATH}

BATMAN_CODE_PATH=${TEST_PATH}/product_code/batman
BATMAN_OUTPUT_PATH=${TEST_PATH}/product_output/batman
BATMAN_MODULES=(transit place common index addr feedback drive walk)
	
#batman
rm -rf ${BATMAN_OUTPUT_PATH}
for module in ${BATMAN_MODULES[@]} 
do
	cd ${BATMAN_CODE_PATH}/$module 
	fisp release -d ${BATMAN_OUTPUT_PATH} --no-color
done

fisp server install pc --root ${BATMAN_OUTPUT_PATH}

cd ${TEST_PATH}
cp ../../*.*.php ${BATMAN_OUTPUT_PATH}/plugin

