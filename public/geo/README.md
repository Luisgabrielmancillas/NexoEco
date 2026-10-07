# Manzanillo municipal boundary

`manzanillo.json` is the unmodified GeoJSON response for municipality 06007
(Manzanillo, Colima), downloaded on 2026-10-07 from INEGI:

https://gaia.inegi.org.mx/wscatgeo/v2/geo/mgem/06007

The response identifies its source as Marco Geoestadístico, diciembre de 2025.
Documentation: https://www.inegi.org.mx/servicios/catalogounico.html

Both server validation and Leaflet use this local snapshot. Validation does not
depend on an external geocoder, typed city names or a rectangular approximation.
When updating the boundary, retain the FeatureCollection / MultiPolygon format,
municipality identifier and source metadata, and run SellerStoreTest.
