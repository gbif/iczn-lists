# iczn-lists
This repository aims to create a valid Darwin Core archive of all names listed in the Official Lists and Indexes of Names in Zoology from the International Commission on Zoological Nomenclature (ICZN). The official source files are available at http://iczn.org/content/official-lists-indexes-1


### PostgreSQL 
Use the sql schema file postgres.sql to create a new tables for the names data.

#### import
\copy names from 'names.txt'

#### export
\copy (select * from names) to 'names.txt'
