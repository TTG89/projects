export function memoize(fn) {
    let cache = {};
    return function(...args) {
      const key = JSON.stringify(args);
      if (Object.prototype.hasOwnProperty.call(cache, key)) {
        return cache[key];
      }
      const value = fn.apply(this, args);
      cache[key] = value;
      return value;
    };
  }