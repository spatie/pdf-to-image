# Changelog

All notable changes to `pdf-to-image` will be documented in this file

## 1.8.2 - 2019-07-31

- add exception message to `PdfDoesNotExist`

## 1.8.1 - 2018-06-02
- throw exception when trying to fetch a negative page number

## 1.8.0 - 2018-04-03
- add method getOutputFormat and update saveImage for auto set filename

## 1.7.0 - 2018-03-14
- make `imagick` public

## 1.6.1 - 2018-03-14
- fix bug around `setCompressionQuality`

## 1.6.0 - 2017-12-20
- add `setCompressionQuality`

## 1.5.0 - 2017-10-11
- add `setColorspace`

## 1.4.6 - 2017-10-11
- fix remote pdf handling

## 1.4.5 - 2017-07-18
- fix flattening of pdf

## 1.4.4 - 2017-07-07
- fix where `getNumberOfPages` would report the wrong number when looping through the pdf

## 1.4.3 - 2017-07-07
- fix bugs introduced in 1.4.2

## 1.4.2 - 2017-07-01
- fix for setting custom resolution

## 1.4.1 - 2017-06-28
- fix `setLayerMethod` method

## 1.4.0 - 2017-06-15
- add `setLayerMethod` method

## 1.3.3 - 2017-04-25
- remove use of `Imagick::LAYERMETHOD_FLATTEN` as it messes up the rendering of specific pages

## 1.3.2 - 2017-04-25
- set default format

## 1.3.1 - 2017-04-16
- performance improvements

## 1.3.0 - 2017-03-23
- allow pdf to be loaded from a URL

## 1.2.2 - 2016-12-14
- improve return value

## 1.2.1 - 2016-09-08
- fix for pdf's with transparent backgrounds

## 1.2.0 - 2016-04-29
- added `saveAllPagesAsImages`-function.

## 1.1.0 - 2015-04-13
- added `getImageData`-function.

## 1.0.3 - 2015-01-22

### Bugfix
- Exceptions now live in the right namespace.

## 1.0.1 - 2015-07-03

### Bugfix
- setPage is now working as excepted.

## 1.0.0 - 2015-07-02

### Added
- It's so first release, so everything was added.

