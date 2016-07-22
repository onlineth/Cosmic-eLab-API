CREATE TABLE api_files
(
    fileid INTEGER PRIMARY KEY NOT NULL,
    detectorid INTEGER NOT NULL,
    year INTEGER NOT NULL,
    monthday INTEGER NOT NULL,
    index INTEGER NOT NULL,
    filetype CHAR(10) NOT NULL,
    active BOOLEAN DEFAULT true NOT NULL
);