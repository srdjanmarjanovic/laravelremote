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
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';
import { toast } from 'vue-sonner';
import { index, read, readAll } from '@/routes/notifications';

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
const unreadCount = computed(() => notifications.value.filter(n => !n.read_at).length);
const isLoading = ref(false);

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

const getNotificationLink = (notification: Notification): string | null => {
    const type = notification.type;
    const data = notification.data;

    if (type.includes('NewApplication') && data.application_id) {
        return `/hr/applications/${data.application_id}`;
    }
    if (type.includes('ApplicationStatusChanged') && data.application_id) {
        return `/developer/applications/${data.application_id}`;
    }
    if (type.includes('Position') && data.position_id) {
        if (user.value?.role === 'hr') {
            return `/hr/positions/${data.position_id}`;
        }
        if (user.value?.role === 'admin') {
            return `/admin/positions`;
        }
    }

    return null;
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
    setInterval(fetchNotifications, 30000);
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
                <div v-else-if="notifications.length === 0" class="p-4 text-center text-sm text-muted-foreground">
                    No notifications
                </div>
                <template v-else>
                    <DropdownMenuItem
                        v-for="notification in notifications"
                        :key="notification.id"
                        :as-child="true"
                        :class="[
                            'flex flex-col items-start gap-1 p-4 cursor-pointer',
                            !notification.read_at && 'bg-accent'
                        ]"
                    >
                        <component
                            :is="getNotificationLink(notification) ? Link : 'div'"
                            :href="getNotificationLink(notification) || undefined"
                            class="w-full"
                            @click="!notification.read_at && markAsRead(notification.id)"
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
                        </component>
                    </DropdownMenuItem>
                </template>
            </div>
            <DropdownMenuSeparator />
            <DropdownMenuItem as-child>
                <Link href="/notifications" class="w-full text-center text-sm">
                    View all notifications
                </Link>
            </DropdownMenuItem>
        </DropdownMenuContent>
    </DropdownMenu>
</template>

