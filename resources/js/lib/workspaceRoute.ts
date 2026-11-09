import type { RouteDefinition } from '@/wayfinder';

type Method = 'get' | 'post' | 'put' | 'delete' | 'patch' | 'head' | 'options';

// Paths, not Wayfinder's protocol-relative URLs, which SSR and the browser resolve differently.
export function onWorkspace<TMethod extends Method>(
    route: RouteDefinition<TMethod>,
): RouteDefinition<TMethod> {
    const url = new URL(route.url, 'http://localhost');

    return { ...route, url: `${url.pathname}${url.search}${url.hash}` };
}
