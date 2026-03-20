<?php

namespace App\Services\Dashboard;

use App\Models\User;

class DashboardOverviewService
{
    /**
     * Get the dashboard overview data.
     *
     * @param User|null $user
     * @return array
     */
    public function getOverviewData(?User $user = null): array
    {
        $displayName = $user ? $user->name : 'Alex Johnson';

        return [
            'heading' => 'Dashboard Overview',
            'subheading' => "Welcome back, {$displayName}. Here's what's happening with your WhatsApp campaigns.",
            'storage' => [
                'percent' => 65,
                'label' => '6.5GB of 10GB used',
            ],
            'topbarUser' => [
                'name' => $displayName,
                'role_label' => 'Admin Account',
                'avatar_url' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuAFipTKFXb18IRIerEL6GHqmAN918JWxMjFwdUV3WciqAb33bEr3MxJcO6uHOy7RpvO907V_SCPMStdzSes7MgvUmOhE5YvTs68W_mcRDXmKISvF0KIgVgBcZwSpPCOoa_ArcE2z0RzlWjZLFpF3n5zmEPTPcj-TLoxPR4uuipZWcsJjgCWcGrdP-D202rHObY54ZNl7DDPypg725MvjPVqjxRBmaLHNBq57ipak77x9aZ7uoYgSPE0wo2Ph8TYa44iITUvivWv1uo',
            ],
            'stats' => [
                [
                    'label' => 'Total Messages',
                    'value' => '128,430',
                    'icon' => 'send',
                    'icon_bg_class' => 'bg-blue-50 dark:bg-blue-900/30',
                    'icon_text_class' => 'text-primary',
                    'badge' => '+12.5%',
                    'badge_class' => 'bg-green-50 text-green-500 dark:bg-green-900/20',
                ],
                [
                    'label' => 'Active Chats',
                    'value' => '452',
                    'icon' => 'forum',
                    'icon_bg_class' => 'bg-emerald-50 dark:bg-emerald-900/30',
                    'icon_text_class' => 'text-emerald-600',
                    'badge' => '+5.2%',
                    'badge_class' => 'bg-green-50 text-green-500 dark:bg-green-900/20',
                ],
                [
                    'label' => 'Connected Numbers',
                    'value' => '12',
                    'icon' => 'phone_iphone',
                    'icon_bg_class' => 'bg-purple-50 dark:bg-purple-900/30',
                    'icon_text_class' => 'text-purple-600',
                    'badge' => 'Static',
                    'badge_class' => 'bg-slate-50 text-slate-400 dark:bg-slate-800',
                ],
                [
                    'label' => 'Templates Count',
                    'value' => '84',
                    'icon' => 'fact_check',
                    'icon_bg_class' => 'bg-amber-50 dark:bg-amber-900/30',
                    'icon_text_class' => 'text-amber-600',
                    'badge' => '+2.1%',
                    'badge_class' => 'bg-green-50 text-green-500 dark:bg-green-900/20',
                ],
            ],
            'chart' => [
                'title' => 'Message Activity',
                'subtitle' => 'Total volume in the last 7 days',
                'total' => '45,200',
                'change' => '+8.4%',
                'change_label' => 'vs previous period',
                'days' => ['MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT', 'SUN'],
            ],
            'activities' => [
                [
                    'title' => 'Sarah Chen',
                    'description' => 'joined the team',
                    'time' => '2 minutes ago',
                    'icon' => 'person_add',
                    'icon_bg_class' => 'bg-slate-100 dark:bg-slate-800',
                    'icon_text_class' => 'text-slate-500 dark:text-slate-400',
                ],
                [
                    'title' => 'Campaign "Welcome Flow"',
                    'description' => 'was broadcasted to 1,200 recipients',
                    'time' => '45 minutes ago',
                    'icon' => 'send',
                    'icon_bg_class' => 'bg-blue-50 dark:bg-blue-900/30',
                    'icon_text_class' => 'text-primary',
                ],
                [
                    'title' => 'Number +1-555-0123',
                    'description' => 'disconnected unexpectedly',
                    'time' => '2 hours ago',
                    'icon' => 'warning',
                    'icon_bg_class' => 'bg-amber-50 dark:bg-amber-900/30',
                    'icon_text_class' => 'text-amber-600',
                ],
                [
                    'title' => 'Template "Order Confirmation"',
                    'description' => 'was approved by Meta',
                    'time' => '5 hours ago',
                    'icon' => 'description',
                    'icon_bg_class' => 'bg-slate-100 dark:bg-slate-800',
                    'icon_text_class' => 'text-slate-500 dark:text-slate-400',
                ],
                [
                    'title' => 'New integration',
                    'description' => 'successfully linked with Shopify',
                    'time' => 'Yesterday',
                    'icon' => 'check_circle',
                    'icon_bg_class' => 'bg-green-50 dark:bg-green-900/30',
                    'icon_text_class' => 'text-green-600',
                ],
            ],
            'campaigns' => [
                [
                    'name' => 'Holiday Special Promo',
                    'category' => 'Marketing Category',
                    'status' => 'Running',
                    'status_class' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-500',
                    'dot_class' => 'bg-green-500',
                    'sent' => '15,400',
                    'opened' => '92%',
                ],
                [
                    'name' => 'Flash Sale Alert',
                    'category' => 'Urgent Category',
                    'status' => 'Scheduled',
                    'status_class' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-500',
                    'dot_class' => 'bg-amber-500',
                    'sent' => '0',
                    'opened' => '-',
                ],
                [
                    'name' => 'Customer Onboarding',
                    'category' => 'Utility Category',
                    'status' => 'Paused',
                    'status_class' => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-400',
                    'dot_class' => 'bg-slate-400',
                    'sent' => '2,300',
                    'opened' => '84%',
                ],
            ],
        ];
    }
}
