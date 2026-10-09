export function distanceKm(origin, destination) {
    const radians = value => value * Math.PI / 180;
    const a = Math.sin(radians(destination[0] - origin[0]) / 2) ** 2
        + Math.cos(radians(origin[0])) * Math.cos(radians(destination[0])) * Math.sin(radians(destination[1] - origin[1]) / 2) ** 2;
    return 6371.0088 * 2 * Math.asin(Math.sqrt(Math.min(1, Math.max(0, a))));
}

export function locationHasDrifted(saved, current, accuracyMeters, thresholdKm) {
    const distance = distanceKm(saved, current);
    return distance - Math.max(0, accuracyMeters) / 1000 > thresholdKm;
}

export function routeDistance(meters) {
    return meters < 1000 ? `${Math.round(meters)} m` : `${(meters / 1000).toLocaleString('es-MX', { maximumFractionDigits: 1 })} km`;
}
