import axios from 'axios';

const api = axios.create({
    baseURL: '/api',
    headers: {
        Accept: 'application/json',
    },
});

export function setAuthToken(token) {
    if (token) {
        api.defaults.headers.common.Authorization = `Bearer ${token}`;
    } else {
        delete api.defaults.headers.common.Authorization;
    }
}

let unauthorizedHandler = null;

export function onUnauthorized(handler) {
    unauthorizedHandler = handler;
}

api.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error?.response?.status === 401 && unauthorizedHandler) {
            unauthorizedHandler();
        }

        return Promise.reject(error);
    }
);

export function extractErrorMessage(error, fallback = "Une erreur est survenue.") {
    const data = error?.response?.data;

    if (!data) {
        return fallback;
    }

    if (data.errors && typeof data.errors === 'object') {
        const first = Object.values(data.errors)[0];
        if (Array.isArray(first) && first.length > 0) {
            return first[0];
        }
    }

    return data.message || fallback;
}

export function extractFieldErrors(error) {
    return error?.response?.data?.errors || {};
}

export default api;
