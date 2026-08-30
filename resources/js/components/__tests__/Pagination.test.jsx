import { describe, it, expect, vi } from 'vitest';
import { render, screen } from '@testing-library/react';
import Pagination from '@/components/Pagination';

const translations = {
    'Pagination': 'Pagination (fr)',
    'Previous': 'Précédent',
    'Next': 'Suivant',
    ':from–:to of :total': ':from–:to sur :total',
    'Page :current of :last': 'Page :current sur :last',
};

vi.mock('@inertiajs/react', () => ({
    usePage: () => ({ props: { translations } }),
    Link: ({ href, children, ...rest }) => <a href={href} {...rest}>{children}</a>,
}));

function paginator(overrides = {}) {
    return {
        current_page: 2,
        last_page: 3,
        from: 11,
        to: 20,
        total: 25,
        prev_page_url: '/vehicle?page=1',
        next_page_url: '/vehicle?page=3',
        ...overrides,
    };
}

describe('Pagination', () => {
    it('renders nothing for a single page', () => {
        const { container } = render(<Pagination paginator={paginator({ last_page: 1 })} />);
        expect(container).toBeEmptyDOMElement();
    });

    it('is a labelled navigation landmark with translated controls', () => {
        render(<Pagination paginator={paginator()} />);
        const nav = screen.getByRole('navigation', { name: 'Pagination (fr)' });
        expect(nav).toBeInTheDocument();
        expect(screen.getByRole('link', { name: 'Précédent' })).toHaveAttribute('href', '/vehicle?page=1');
        expect(screen.getByRole('link', { name: 'Suivant' })).toHaveAttribute('href', '/vehicle?page=3');
        expect(screen.getByText('11–20 sur 25')).toBeInTheDocument();
        expect(screen.getByText('Page 2 sur 3')).toBeInTheDocument();
    });

    it('renders an empty range instead of "null" past the last page', () => {
        render(<Pagination paginator={paginator({ current_page: 9, from: null, to: null, next_page_url: null })} />);
        expect(screen.getByText('– sur 25')).toBeInTheDocument();
    });

    it('disables the bound control instead of rendering a dead link', () => {
        render(<Pagination paginator={paginator({ current_page: 3, prev_page_url: '/vehicle?page=2', next_page_url: null })} />);
        expect(screen.getByRole('button', { name: 'Suivant' })).toBeDisabled();
        expect(screen.queryByRole('link', { name: 'Suivant' })).toBeNull();
        expect(screen.getByRole('link', { name: 'Précédent' })).toBeInTheDocument();
    });
});
