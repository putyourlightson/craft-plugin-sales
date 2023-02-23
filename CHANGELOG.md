# Release Notes for Plugin Sales

## 1.4.0 - 2023-02-22
### Added
- Added a new optional organisation ID setting.

### Changed
- Updated the API to use Craft Console and organisations.

## 1.3.10 - 2022-01-26
### Changed
- Exchange rates are now fetched from the API at most once per day, to help avoid exceeding monthly request limits.  

## 1.3.9 - 2021-09-14
### Fixed
- Fixed a bug that was throwing an error when a plugin did not have any sales.  

## 1.3.8 - 2021-09-01
### Changed
- Improved the outputting of converted net amount.  

## 1.3.7 - 2021-08-26
### Fixed
- Fixed individual sales being converted when they shouldn't be.

## 1.3.6 - 2021-08-26
### Changed
- Updated all JavaScript libraries and now load resources from a CDN.

### Fixed
- Fixed the Y-axis labels when amounts were being converted.

## 1.3.5 - 2021-08-26
### Changed
- Switched from using ratesapi.io to freecurrencyapi.net for converting currencies.
- Improved wording of search results in DataTables.

### Fixed
- Fixed logs not being written to `plugin-sales.log` file.

## 1.3.4 - 2021-05-11
### Changed
- Switched from using exchangeratesapi.io to ratesapi.io for converting currencies.

## 1.3.3 - 2021-05-11
### Changed
- Improved the help text for console commands.

### Fixed
- Fixed colour palette values not being prefixed with a hash.

## 1.3.2 - 2020-05-23
### Changed
- Updated ApexCharts to v3.19.2 which fixes a minor bug in the `Download CSV` feature.

## 1.3.1 - 2020-04-06
### Fixed
- Fixed amounts in charts not being converted using the exchange rate.

## 1.3.0 - 2020-04-06
### Added
- Added renewals to monthly sales report.

### Changed
- Changed wording of number of sales.

### Fixed
- Fixed undefined currency error when settings were not saved after a recent update.

## 1.2.2 - 2020-03-23
### Fixed
- Fixed bug in format value method.

## 1.2.1 - 2020-03-23
### Added
- Added info window with total net profit in original currency and exchange rate if not USD.

### Changed
- Changed predefined selectable date ranges.

## 1.2.0 - 2020-03-16
### Added
- Added a currency setting so that sales are converted to a live exchange rate.
- Added progress status to refresh sales jobs in the control panel.

### Fixed
- Fixed formatting of licenses in monthly sales chart tooltip.

## 1.1.4 - 2020-03-13
### Changed
- General UI improvements.

### Fixed
- Fixed localisation of last refresh date.

## 1.1.3 - 2020-03-11
### Added
- Added logging of failed plugin sale refreshes.

### Changed
- General UI improvements.

## 1.1.2 - 2020-03-10
### Changed
- Improved performance of generating charts and reports.

### Fixed
- Fixed timeout issue when filtering by some date ranges.

## 1.1.1 - 2020-03-10
### Fixed
- Fixed parsing of environment variables in plugin settings.

## 1.1.0 - 2020-03-10
### Added
- Added a colour palette to plugin settings.
- Added console commands to refresh and delete sales.
- Added currency and site locale formatting to amounts.

### Changed
- General UI improvements.

## 1.0.0 - 2020-03-09
- Initial release.
