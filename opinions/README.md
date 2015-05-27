# Opinions

This folder contains data and scripts for linking Opinion and Declarations of the ICZN to the corresponding publication in BioStor. The goal is to have complete bibliographic metadata and link to full text for every Opinion and Declaration. 

Processing of the data originally used a Google spreadsheet https://docs.google.com/spreadsheets/d/1ivfmcac5IC5BhUWCOgOYEKdZQNQSrAzkoNy4GJEGiVc/edit?usp=sharing together with some scripts (the *.gs files in the “google-sheets” folder). The ImportJSON.gs script comes from https://github.com/fastfedora/google-docs.

However, this approach didn’t scale very well so I ended up putting the data into a local MySQL database and writing some PHP scripts to clean and link the referenced (in the “opinions-php” folder)

## Results

The mapping between publications and BioStor/BHL is available in several formats.

The first is a RIS dump that can be read by standard bibliographic software. This file was used to create the Mendeley group https://www.mendeley.com/groups/7117941/iczn/

The second output is a tab-delimited file with the first row containing column headers. Each reference has a locally unique identifier of the form [d|o]\d+ where “o” is Opinion, “d” is “Direction” and \d+ is the number. Headings (and suggested mappings based on EOL References Extension http://tools.gbif.org/dwca-validator/extension.do?id=http://eol.org/schema/reference/Reference ):

key     | value
--------|---------------------------------------------
id      | http://purl.org/dc/terms/identifier
creator | http://purl.org/dc/terms/creator
title   | http://eol.org/schema/reference/primaryTitle
journal | http://purl.org/dc/terms/title
issn    | http://purl.org/ontology/bibo/issn
oclc    | http://purl.org/ontology/bibo/oclcnum
volume  | http://purl.org/ontology/bibo/volume
issue   | http://purl.org/ontology/bibo/issue
spage   | http://purl.org/ontology/bibo/pageStart
epage   | http://purl.org/ontology/bibo/pageEnd
biostor | http://purl.org/ontology/bibo/uri
bhl     | http://purl.org/ontology/bibo/uri

lastly, there is a dump of the MySQL database that contains the mapping.


