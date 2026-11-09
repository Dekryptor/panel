// Object.hasOwn is missing before Safari 15.4 and Chrome 93, and Inertia's router calls it.
if (typeof Object.hasOwn !== 'function') {
    Object.defineProperty(Object, 'hasOwn', {
        value: (object: object, key: PropertyKey): boolean =>
            Object.prototype.hasOwnProperty.call(Object(object), key),
        configurable: true,
        writable: true,
    });
}
