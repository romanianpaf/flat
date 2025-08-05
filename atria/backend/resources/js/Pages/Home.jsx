import GuestLayout from '../Layouts/GuestLayout';
import { Head, Link } from '@inertiajs/react';

export default function Home() {
    return (
        <GuestLayout>
            <Head title="Login" />
            {/* TODO: Replace with styled login form based on modele/layouts/authentication/sign-in/basic */}
            <div className="min-h-screen flex flex-col justify-center items-center bg-gray-50 py-6">
                <h1 className="text-3xl font-bold mb-6">Autentificare</h1>
                <form method="post" action="/login" className="w-full max-w-sm bg-white p-6 rounded shadow">
                    <div className="mb-4">
                        <label className="block mb-1 font-medium" htmlFor="email">Email</label>
                        <input id="email" name="email" type="email" required className="w-full border rounded px-3 py-2" />
                    </div>
                    <div className="mb-6">
                        <label className="block mb-1 font-medium" htmlFor="password">Parolă</label>
                        <input id="password" name="password" type="password" required className="w-full border rounded px-3 py-2" />
                    </div>
                    <button type="submit" className="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 rounded">Conectează-te</button>
                </form>
            </div>
        </GuestLayout>
    );
}
