<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Bell, Check, CheckCheck } from 'lucide-vue-next';
import { router, usePage } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { toast } from 'vue-sonner';
import { index, read, readAll } from '@/routes/notifications';
import hrApplications from '@/routes/hr/applications';
import developerApplications from '@/routes/developer/applications';
import hrPositions from '@/routes/hr/positions';
import adminPositions from '@/routes/admin/positions';

interface Notification {
    id: string;
    type: string;
    data: Record<string, any>;
    read_at: string | null;
    created_at: string;
}

const page = usePage();
const auth = computed(() => page.props.auth);
const user = computed(() => auth.value?.user);

const notifications = ref<Notification[]>([]);
const filteredNotifications = computed(() => {
    // Filter out ApplicationStatusChanged notifications for developers
    if (user.value?.role === 'developer') {
        return notifications.value.filter(n => !n.type.includes('ApplicationStatusChanged'));
    }
    return notifications.value;
});
const unreadCount = computed(() => filteredNotifications.value.filter(n => !n.read_at).length);
const isLoading = ref(false);
let pollInterval: ReturnType<typeof setInterval> | null = null;

const fetchNotifications = async () => {
    if (!user.value) {
        return;
    }

    isLoading.value = true;
    try {
        const response = await fetch(index.url({ query: { limit: 10 } }), {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });
        const data = await response.json();
        notifications.value = data.data || [];
    } catch (error) {
        console.error('Failed to fetch notifications:', error);
    } finally {
        isLoading.value = false;
    }
};

const markAsRead = (notificationId: string) => {
    router.post(read.url(notificationId), {}, {
        preserveScroll: true,
        onSuccess: () => {
            const notification = notifications.value.find(n => n.id === notificationId);
            if (notification) {
                notification.read_at = new Date().toISOString();
            }
        },
        onError: () => {
            toast.error('Failed to mark notification as read');
        },
    });
};

const markAllAsRead = () => {
    router.post(readAll.url(), {}, {
        preserveScroll: true,
        onSuccess: () => {
            notifications.value.forEach(n => {
                n.read_at = new Date().toISOString();
            });
            toast.success('All notifications marked as read');
        },
        onError: () => {
            toast.error('Failed to mark all notifications as read');
        },
    });
};

const getNotificationTitle = (notification: Notification): string => {
    const type = notification.type;
    const data = notification.data;

    if (type.includes('NewApplication')) {
        return 'New Application';
    }
    if (type.includes('ApplicationStatusChanged')) {
        return 'Application Status Updated';
    }
    if (type.includes('PositionExpiring')) {
        return 'Position Expiring Soon';
    }
    if (type.includes('PositionExpired')) {
        return 'Position Expired';
    }
    if (type.includes('PositionPublished')) {
        return 'Position Published';
    }
    if (type.includes('PositionUpgraded')) {
        return 'Position Upgraded';
    }
    if (type.includes('Welcome')) {
        return 'Welcome!';
    }

    return 'Notification';
};

const getNotificationMessage = (notification: Notification): string => {
    const data = notification.data;

    if (data.position_title) {
        return data.position_title;
    }
    if (data.applicant_name) {
        return `${data.applicant_name} applied`;
    }

    return 'You have a new notification';
};

const handleNotificationClick = (notification: Notification) => {
    const type = notification.type;
    const data = notification.data;
    let url: string | null = null;

    if (type.includes('NewApplication') && data.application_id) {
        url = hrApplications.show(data.application_id).url;
    } else if (type.includes('ApplicationStatusChanged') && data.application_id) {
        // Don't navigate developers to application detail pages for status changes
        if (user.value?.role !== 'developer') {
            url = developerApplications.show(data.application_id).url;
        }
    } else if (type.includes('Position') && data.position_id) {
        if (user.value?.role === 'hr') {
            url = hrPositions.show(data.position_id).url;
        } else if (user.value?.role === 'admin') {
            url = adminPositions.index().url;
        }
    }

    if (url) {
        // Mark as read if unread
        if (!notification.read_at) {
            markAsRead(notification.id);
        }
        // Navigate to the page
        router.visit(url);
    }
};

const formatTime = (dateString: string): string => {
    const date = new Date(dateString);
    const now = new Date();
    const diffInSeconds = Math.floor((now.getTime() - date.getTime()) / 1000);

    if (diffInSeconds < 60) {
        return 'Just now';
    }
    if (diffInSeconds < 3600) {
        const minutes = Math.floor(diffInSeconds / 60);
        return `${minutes}m ago`;
    }
    if (diffInSeconds < 86400) {
        const hours = Math.floor(diffInSeconds / 3600);
        return `${hours}h ago`;
    }
    if (diffInSeconds < 604800) {
        const days = Math.floor(diffInSeconds / 86400);
        return `${days}d ago`;
    }

    return date.toLocaleDateString();
};

onMounted(() => {
    fetchNotifications();
    // Poll for new notifications every 30 seconds
    pollInterval = setInterval(fetchNotifications, 30000);
});

onUnmounted(() => {
    if (pollInterval) {
        clearInterval(pollInterval);
    }
});
</script>

<template>
    <DropdownMenu>
        <DropdownMenuTrigger as-child>
            <Button
                variant="ghost"
                size="icon"
                class="relative h-9 w-9"
            >
                <Bell class="h-5 w-5" />
                <Badge
                    v-if="unreadCount > 0"
                    variant="destructive"
                    class="absolute -right-1 -top-1 flex h-5 w-5 items-center justify-center rounded-full p-0 text-xs"
                >
                    {{ unreadCount > 9 ? '9+' : unreadCount }}
                </Badge>
            </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent align="end" class="w-80">
            <div class="flex items-center justify-between p-4">
                <h3 class="font-semibold">Notifications</h3>
                <Button
                    v-if="unreadCount > 0"
                    variant="ghost"
                    size="sm"
                    class="h-8 text-xs"
                    @click="markAllAsRead"
                >
                    <CheckCheck class="mr-1 h-3 w-3" />
                    Mark all read
                </Button>
            </div>
            <DropdownMenuSeparator />
            <div class="max-h-[400px] overflow-y-auto">
                <div v-if="isLoading" class="p-4 text-center text-sm text-muted-foreground">
                    Loading...
                </div>
                <div v-else-if="filteredNotifications.length === 0" class="p-4 text-center text-sm text-muted-foreground">
                    No notifications
                </div>
                <template v-else>
                    <DropdownMenuItem
                        v-for="notification in filteredNotifications"
                        :key="notification.id"
                        :class="[
                            'flex flex-col items-start gap-1 p-4 cursor-pointer',
                            !notification.read_at && 'bg-accent'
                        ]"
                        @click="handleNotificationClick(notification)"
                    >
                        <div class="flex w-full items-start justify-between gap-2">
                            <div class="flex-1">
                                <p class="text-sm font-medium">
                                    {{ getNotificationTitle(notification) }}
                                </p>
                                <p class="text-xs text-muted-foreground mt-1">
                                    {{ getNotificationMessage(notification) }}
                                </p>
                                <p class="text-xs text-muted-foreground mt-1">
                                    {{ formatTime(notification.created_at) }}
                                </p>
                            </div>
                            <Button
                                v-if="!notification.read_at"
                                variant="ghost"
                                size="icon"
                                class="h-6 w-6"
                                @click.stop="markAsRead(notification.id)"
                            >
                                <Check class="h-3 w-3" />
                            </Button>
                        </div>
                    </DropdownMenuItem>
                </template>
            </div>
            <DropdownMenuSeparator />
            <DropdownMenuItem
                class="cursor-pointer"
                @click="router.visit(index.url())"
            >
                <span class="w-full text-center text-sm">
                    View all notifications
                </span>
            </DropdownMenuItem>
        </DropdownMenuContent>
    </DropdownMenu>
</template>

