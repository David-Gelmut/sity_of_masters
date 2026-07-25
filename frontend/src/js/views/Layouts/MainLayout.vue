<template>
  <div>
    <Sidebar />
    <div
        :class="$route.name === 'Chat' ? 'overflow-hidden overscroll-none' : ''"
        class="flex flex-col min-h-screen bg-slate-50 font-sans">
      <Header />
        <main class="flex-1 pl-2 pr-2 md:p-8 w-full mx-auto">
          <router-view v-slot="{ Component }">
            <component :is="Component" />
          </router-view>
        </main>
    </div>
    <!--Footer /-->
    <ToastContainer />
  </div>
</template>
<script setup>
import { useRouter } from 'vue-router';
import Sidebar from '../../components/Sidebar.vue';
import Header from '../../components/Header.vue';
import Footer from '../../components/Footer.vue';
import { useAuthStore } from '../../store/auth.js';
import { useToastStore } from '../../store/toast.js';
import { useChatStore } from '../../store/chat.js';
import ToastContainer from '../../components/ToastContainer.vue';
import {onMounted, onUnmounted} from "vue";


const authStore = useAuthStore();
const toastStore = useToastStore();
const chatStore = useChatStore();
const router = useRouter();

onMounted(() => {

  authStore.getUser();
  const currentUserId = authStore.user?.id;

  if (currentUserId && window.Echo) {
    window.Echo.private(`user.${currentUserId}`)
        .listen('MessageSent', (e) => {
          console.log('Глобальное фоновое уведомление:', e);

          chatStore.incrementUnreadCount(e.chat_id);

        //  const isChatPage = router.currentRoute.value.name === 'ChatPage';

         // if (!isChatPage) {
            toastStore.add(`Новое сообщение от ${e.user_name}`, e.text, 'info');
         /// }
        });
  }
});

onUnmounted(() => {
  if (authStore.user?.id && window.Echo) {
    window.Echo.leaveChannel(`user.${authStore.user.id}`);
  }
});
</script>