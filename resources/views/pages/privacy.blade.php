@component('layouts.auth')
<div class="min-h-screen bg-slate-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-3xl mx-auto bg-white p-8 rounded-2xl shadow-sm border border-slate-200">
        <h1 class="text-3xl font-black text-slate-900 mb-6">Privacy Policy</h1>
        
        <div class="prose prose-slate max-w-none text-slate-600 space-y-6">
            <p>Last updated: {{ now()->format('F j, Y') }}</p>

            <section>
                <h2 class="text-xl font-bold text-slate-900 mb-3">1. Information We Collect</h2>
                <p>We collect information that you provide directly to us, such as when you create or modify your account, request on-demand services, contact customer support, or otherwise communicate with us. This information may include: name, email, phone number, postal address, profile picture, payment method, and other information you choose to provide.</p>
            </section>

            <section>
                <h2 class="text-xl font-bold text-slate-900 mb-3">2. How We Use Your Information</h2>
                <p>We may use the information we collect about you to provide, maintain, and improve our services, including to facilitate payments, send receipts, provide products and services you request, develop new features, provide customer support to Users, develop safety features, authenticate users, and send product updates and administrative messages.</p>
            </section>

            <section>
                <h2 class="text-xl font-bold text-slate-900 mb-3">3. Sharing of Information</h2>
                <p>We may share the information we collect about you as described in this policy or as described at the time of collection or sharing, including as follows: with third party Service Providers; with the general public if you submit content in a public forum; in response to a request for information by a competent authority if we believe disclosure is in accordance with, or is otherwise required by, any applicable law, regulation, or legal process.</p>
            </section>

            <section>
                <h2 class="text-xl font-bold text-slate-900 mb-3">4. Security</h2>
                <p>We take reasonable measures to help protect information about you from loss, theft, misuse and unauthorized access, disclosure, alteration and destruction.</p>
            </section>

            <section>
                <h2 class="text-xl font-bold text-slate-900 mb-3">5. Contact Us</h2>
                <p>If you have any questions about this Privacy Policy, please contact us at support@example.com.</p>
            </section>

            <div class="pt-8 border-t border-slate-100 mt-8">
                <a href="{{ route('home') }}" class="text-primary hover:underline font-medium">&larr; Back to Home</a>
            </div>
        </div>
    </div>
</div>
@endcomponent
