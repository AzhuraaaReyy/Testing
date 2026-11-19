type ElementConstructor<T extends Element> = { new (): T; prototype: T };

/**
 * querySelector bertipe: mengembalikan elemen hanya jika cocok dengan tipe yang diminta,
 * sehingga tidak perlu type assertion (`as`) di komponen.
 */
export function query<T extends Element>(root: ParentNode, selector: string, type: ElementConstructor<T>): T | null {
    const element = root.querySelector(selector);
    return element instanceof type ? element : null;
}

export function queryAll<T extends Element>(root: ParentNode, selector: string, type: ElementConstructor<T>): T[] {
    return Array.from(root.querySelectorAll(selector)).filter((element): element is T => element instanceof type);
}

/** Elemen terdekat dari target event yang cocok dengan selector dan tipe. */
export function closestFromEvent<T extends Element>(event: Event, selector: string, type: ElementConstructor<T>): T | null {
    if (!(event.target instanceof Element)) return null;
    const element = event.target.closest(selector);
    return element instanceof type ? element : null;
}
