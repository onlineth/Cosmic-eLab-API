CREATE TABLE public.api_files (
  fileid INTEGER PRIMARY KEY NOT NULL,
  detectorid INTEGER NOT NULL,
  year INTEGER NOT NULL,
  monthday INTEGER NOT NULL,
  index INTEGER NOT NULL,
  filetype CHARACTER(10) NOT NULL,
  active BOOLEAN NOT NULL DEFAULT true
);
CREATE SEQUENCE public.api_files_fileid_seq NO MINVALUE NO MAXVALUE NO CYCLE;
ALTER TABLE public.api_files ALTER COLUMN fileid SET DEFAULT nextval('public.api_files_fileid_seq');
ALTER SEQUENCE public.api_files_fileid_seq OWNED BY public.api_files.fileid;