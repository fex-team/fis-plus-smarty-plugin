#!/usr/bin/env bash

TEST_PATH=/home/work/repos/fis-plus-smarty-plugin/test/performance
cd ${TEST_PATH}

BATMAN_SVN=https://svn.baidu.com/app/search/lbs-webapp/trunk/mmap/batman
BATMAN_DIR=./product_code/batman

svn co --username=tianlili --password=tianlili --no-auth-cache ${BATMAN_SVN} ${BATMAN_DIR}
