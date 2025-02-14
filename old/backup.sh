#!/bin/bash

mysqldump -u db -p cuentosBD --routines --triggers --events --no-create-db --no-create-info > backup.sql
