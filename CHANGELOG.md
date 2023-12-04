# Release Notes for Plugin Sales

## 2.8.0 - Unreleased

### Added

- Added Sprig core in place of Datatables, for much more optimal loading of sales.

### Changed

- Redirecting to the plugin settings page is now only attempted when installing via the control panel.

## 2.7.3 - 2023-07-11

### Changed

- Customer domain names are now only linked if not an email provider.

## 2.7.2 - 2023-07-10

### Changed

- Customer names are now only linked if a domain name is detected.

## 2.7.1 - 2023-07-10

### Added

- Added compatibility with the organisation customer feature in Craft Console.
- Added “Last 30 Days” and “Last 12 Months” options to the date range picker.

## 2.7.0 - 2023-06-12

### Added

- Added a link to the email domain name in slideouts.

### Fixed

- Fixed the focus not being removed when the slideout opens.

## 2.6.2 - 2023-02-13

### Fixed

- Fixed a bug in which plugins purchased together could inadvertently mark non-first purchases as first purchases.

## 2.6.1 - 2023-02-07

### Changed

- Hid x-axis lines which were enabled in recent versions of ApexCharts.

## 2.6.0 - 2022-12-15

### Added

- Added a badge for first license purchases.

## 2.5.1 - 2022-11-29

### Changed

- Changed "Cancel" button to read "Close" in slideout.

## 2.5.0 - 2022-11-29

### Added

- Added detailed customer views that open in a slideout.

## 2.4.0 - 2022-11-27

### Added

- Added a notice for upgrade discounts and extended licenses.

## 2.3.2 - 2022-11-17

### Changed

- Switched from using currencyapi.com to exchangerate.host for converting currencies.

## 2.3.1 - 2022-11-13

### Changed

- Auto increment values are now reset when deleting all rows in database tables.

## 2.3.0 - 2022-11-04

### Added

- Added a new optional organisation ID setting.

### Changed

- Updated the API to use Craft Console and organisations.

## 2.2.0 - 2022-05-25

### Added

- Added a new customers report showing aggregated sales.

## 2.1.0 - 2022-05-23

> {note} This update modifies the default colour palette. If you've customised either of the first 2 colours then the update will have no effect.

### Added

- Added a config setting that controls the number of colours to display in the colour palette.

### Changed

- Updated the default colour palette ([#2](https://github.com/putyourlightson/craft-campaign/issues/2)).
- Updated the plugin icon to match the new default colour palette

## 2.0.1 - 2022-05-21

### Changed

- Aligned pie charts horizontally on desktop screens.

## 2.0.0 - 2022-05-04

### Added

- Added compatibility with Craft 4.

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

- Switched from using ratesapi.io to currencyapi.com for converting currencies.
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
