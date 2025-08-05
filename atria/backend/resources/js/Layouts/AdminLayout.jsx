import { Link } from '@inertiajs/react';

export default function AdminLayout({ children }) {
    return (
        <div className="min-h-screen flex">
            <aside className="w-64 bg-gray-800 text-white p-4">
                <nav className="flex flex-col gap-2">
                    <Link href="/admin" className="py-2 px-3 rounded bg-gray-700">Administrare</Link>
                </nav>
            </aside>
            <main className="flex-1 p-6 bg-gray-50">
                {children}
            </main>
        </div>
    );
}
