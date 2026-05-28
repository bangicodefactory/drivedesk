import { render, screen } from '@testing-library/react';
import { Button } from '@/components/ui/button';

describe('Button', () => {
    it('renders and is accessible in the document', () => {
        render(<Button>Click me</Button>);
        expect(screen.getByRole('button', { name: 'Click me' })).toBeInTheDocument();
    });

    it('applies destructive variant classes', () => {
        render(<Button variant="destructive">Delete</Button>);
        const btn = screen.getByRole('button', { name: 'Delete' });
        expect(btn).toHaveClass('bg-destructive');
    });

    it('is disabled when the disabled prop is set', () => {
        render(<Button disabled>Save</Button>);
        expect(screen.getByRole('button', { name: 'Save' })).toBeDisabled();
    });

    it('renders as a child element when asChild is true', () => {
        render(
            <Button asChild>
                <a href="/home">Go home</a>
            </Button>
        );
        expect(screen.getByRole('link', { name: 'Go home' })).toBeInTheDocument();
    });
});
