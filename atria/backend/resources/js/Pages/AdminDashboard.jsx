import AdminLayout from '../Layouts/AdminLayout';
import { Head } from '@inertiajs/react';

export default function AdminDashboard() {
    return (
        <AdminLayout>
            <Head title="Administrare" />
            <h1 className="text-2xl font-bold mb-4">Panou Administrare</h1>
            {/* Conținut administrare aici */}
            <p>Bun venit, administrator!</p>
        </AdminLayout>
    );
}
