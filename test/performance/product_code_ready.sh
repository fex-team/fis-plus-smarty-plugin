#!/usr/bin/env bash

TEST_PATH=/home/work/repos/fis-plus-smarty-plugin/test/performance
cd ${TEST_PATH}

IKNOW_SVN=https://svn.baidu.com/app/search/iknow/branches/fe/search/iknow_1002-0-86_BRANCH
IKNOW_DIR=./product_code/iknow

svn co --username=tianlili --password=tianlili --no-auth-cache ${IKNOW_SVN} ${IKNOW_DIR}
