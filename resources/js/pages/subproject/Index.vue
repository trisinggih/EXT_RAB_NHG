<script setup lang="ts">
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Table, TableBody, TableCaption, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { Rocket, Pen, Trash } from 'lucide-vue-next';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'users',
        href: '/users',
    },
];

const page = usePage();

interface Users {
    id: number;
    name: string;
    email: string;
    role: string;
}
interface Props {
    users: Users[];
}
const props = defineProps<Props>();

const handleUserDeletion = (userId: number) => {
    if (confirm('Are you sure you want to delete this user??')) {
        router.delete(route('users.destroy', {id: userId}));
    }
}
</script>

<template>
    <Head title="Users" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4">
            <div v-if="page.props.flash.message" class="mb-4">
                <Alert class="bg-blue-200">
                    <Rocket class="size-4" />
                    <AlertTitle>Notification</AlertTitle>
                    <AlertDescription>
                        {{ page.props.flash.message }}
                    </AlertDescription>
                </Alert>
            </div>

            <div>
                <Link :href="route('users.create')">
                    <Button>Create a user</Button>
                </Link>
            </div>

            <div class="mt-10">
                <Table>
                    <TableCaption>A list of your users.</TableCaption>
                    <TableHeader>
                        <TableRow>
                            <TableHead class="w-[100px]"> ID </TableHead>
                            <TableHead>Name</TableHead>
                            <TableHead>Email</TableHead>
                            <TableHead>Role</TableHead>
                            <TableHead class="text-center"> Action </TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="user in users" :key="user.id">
                            <TableCell>{{ user.id }}</TableCell>
                            <TableCell class="font-medium">{{ user.name }}</TableCell>
                            <TableCell>{{ user.email }}</TableCell>
                            <TableCell>{{ user.role }}</TableCell>
                            <TableCell class="text-center space-x-1">
                                <Link :href="route('users.edit', {id: user.id})">
                                    <Button class="bg-slate-500">
                                        <Pen />
                                    </Button>
                                </Link>
                                <Button class="bg-red-600" @click="handleUserDeletion(user.id)">
                                    <Trash />
                                </Button>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
        </div>
    </AppLayout>
</template>
