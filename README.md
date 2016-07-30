# Cosmic Ray eLab API v1
### Thomas Hein

---

This is an API written for the Cosmic Ray eLab to provided an easier way of searching and downloading data files.

## Setup

1. Clone the repository in the directory you want it in: `git clone https://github.com/onlineth/Cosmic-eLab-API.git .`
1. Rename the `config.sample.php` and change it to `config.php`
1. Set the `$data_location` variable to the correct location of the data directory
1. Set the database credentials to their appropriate values
1. Execute the createTables.sql in your database to create the table for the API
1. Run the buildFileDatabase.php in your browser. Note: This process may take time and you may have to increase php's [max execution time](https://php.net/manual/en/info.configuration.php#ini.max-execution-time) located in your php.ini file - when this completes, you should see a message displaying the number of files added to the table without any errors

# How to use

This API uses the JSON format on returning information regardless of the information (with the exception of downloading files and really big errors on the server's end). Each part of the API uses this basic structure:

```
{
	request: {
		pass: "true"
	},
	main: {
		searchfiles: "true"
}
```

Always check if `pass` in the `request` object is true. If it is not, there will be a message after `pass` displaying an error.

Every part of this API uses the GET method. For example, a request for searching files may look like this: `/v1/searchfiles.php?detectorid=6147&year=2010&startmonthday=0415&endmonthday=1208`.

## SearchFiles.php

Used to find files given on specific parameters

### Parameters

#### Required

- `detectorid` - A four digit code that's unique to every detector, example `6147`.
- `year` - Must be one year at a time, example `2010`.

Either one Month and Day:

- `monthday` - A four digit number where the first two digits represent the month and the last two represent the day, example `0204`

Or a range of months/days

- `startmonthday` - The first monthday to include in the range
- `endmonthday` - The last monthday to include in the range

#### Optional parameters

- `index` - Only look for specific indexes of a data file
- `filetype` - Only look for specific file types

Commone file types include:

- raw - Note: Raw is not .raw but for example `6179.2008.0313.3`
- thresh
- bless
- analyze
- geo

### Example response

```
{
	request: {
		pass: "true"
	},
	main: {
		searchfiles: "true"
	},
	filelist: [
		{
			fileid: "4604",
			detectorid: "6147",
			year: "2010",
			monthday: "415",
			index: "0",
			filetype: "raw "
		},
		{
			fileid: "4605",
			detectorid: "6147",
			year: "2010",
			monthday: "415",
			index: "0",
			filetype: "thresh "
		}
	],
	numberOfFiles: 2,
	limit: 500
}
```

## FileInfo.php

Used to retrieve information about a particular file. Can also be used to make sure the file is on the disk before actually downloading it. The output includes if it was found, and if it was, providing information about the file like the DetectorID, Year, MonthDay, Index, and FileType.

### Parameters

#### Required

- `detectorid` - A four digit code that's unique to every detector, example `6147`
- `year` - Must be one year at a time, example `2010`
- `monthday` - A four digit number where the first two digits represent the month and the last two represent the day, example `0204`
- `index` - A specific index of a file, example `0`
- `filetype` - The file type of the file, example `thresh`

Or

- `fileid` - given from a searchfiles.php query, this is just a more convenient way of downloading the file

### Example Response

```
{
	request: {
		pass: "true"
	},
	main: {
		filefound: "true",
		fileinfo: {
			fileid: "4576",
			detectorid: "6147",
			year: "2010",
			monthday: "0404",
			index: "0",
			filetype: "raw"
		}
	}
}
```

## GetFile.php

This script will allow you to download a given file provided it's unique parameters. This file has been created so it allows you to download the data file keeping it's filename intact.

### Parameters

- `detectorid` - A four digit code that's unique to every detector, example `6147`
- `year` - Must be one year at a time, example `2010`
- `monthday` - A four digit number where the first two digits represent the month and the last two represent the day, example `0204`
- `index` - A specific index of a file, example `0`
- `filetype` - The file type of the file, example `thresh`

Or

- `fileid` - given from a searchfiles.php query, this is just a more convenient way of downloading the file

# Issues & Feature Requests

Submit an issue or a feature request [here](https://github.com/onlineth/Cosmic-eLab-API/issues). Also, feel free to fork, improve, and submit a pull request if you'd like help out on this project.

# License

Licensed under the [GNU AFFERO GENERAL PUBLIC LICENSE](https://github.com/onlineth/Cosmic-eLab-API/blob/master/LICENSE)
