import test from 'node:test';
import assert from 'node:assert/strict';
import { distanceKm, locationHasDrifted, routeDistance } from '../../resources/js/discovery-geo.js';

test('location comparison recognises travel without warning about GPS noise', () => {
    const saved = [19.0522, -104.3158];
    assert.equal(locationHasDrifted(saved, [19.0523, -104.3159], 100, 5), false);
    assert.equal(locationHasDrifted(saved, [19.243, -103.724], 50, 5), true);
    // Rough geolocation whose uncertainty covers the saved point is not evidence of movement.
    assert.equal(locationHasDrifted(saved, [19.105, -104.3158], 2000, 5), false);
});

test('distances stay finite at identical and antipodal coordinates and format metres and kilometres', () => {
    assert.equal(distanceKm([0, 0], [0, 0]), 0);
    assert.ok(Math.abs(distanceKm([0, 0], [0, 180]) - 20015.1144) < .01);
    assert.equal(routeDistance(340), '340 m');
    assert.equal(routeDistance(2100), '2.1 km');
});
