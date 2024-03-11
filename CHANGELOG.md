# Release Notes for Plugin Sales

## 2.8.3 - 2024-03-11

### Changed

- Locked ApexCharts to version 3.46, to prevent it breaking multi-axis charts.

## 2.8.2 - 2023-12-20

### Added

- Added a refresh sales job TTR config setting, defaulting to 1 hour.

## 2.8.1 - 2023-12-05

### Fixed

- Fixed a bug in which refreshing sales was throwing an exception.

## 2.8.0 - 2023-12-04

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
