import {defineStore} from 'pinia';
import axios from 'axios';

export const useChatStore = defineStore('chat', {
    state: () => ({
        chatList: [],
        usersList: [],
        activeChatId: null,
        activeInterlocutor: null,
        messages: [],
        groupsMessages: [],
        nextPageUrl: null
    }),

    actions: {
        async fetchChats() {
            try {
                const response = await axios.get('/api/chats');
                this.chatList = response.data;
            } catch (error) {
                console.error('Ошибка загрузки чатов:', error);
            }
        },
        async fetchUsersList() {
            try {
                const response = await axios.get('/api/chats/users');
                this.usersList = response.data;
            } catch (error) {
                console.error('Ошибка загрузки контактов:', error);
            }
        },
        // Загрузка истории сообщений из базы данных
        async loadMessages(chatId) {
            try {
                const response = await axios.get(`/api/chats/${chatId}/messages`);
                this.messages = response.data.data;
                this.groupsMessages = this.groupedMessages(this.messages);
                this.nextPageUrl = response.data.next_page_url
            } catch (error) {
                console.error('Ошибка загрузки сообщений:', error);
            }
        },
        groupedMessages(messages){


            if (!messages || messages.length === 0) return {};

            const groups = {};
            messages.forEach(msg => {

                if (msg) {
                    // Форматируем дату красиво ("22 июля 2026 г.")
                    const date = new Date(msg.created_at).toLocaleDateString('ru-RU', {
                        day: 'numeric',
                        month: 'long',
                        year: 'numeric'
                    });

                    if (!groups[date]) groups[date] = [];
                    groups[date].push(msg);
                }
            });

            return groups;
        },
        // Отправка нового сообщения на бэкенд
        async sendMessageAction(chatId, formData) {

            try {
                const response = await axios.post(`/api/chats/${chatId}/messages`, formData, {
                    headers: {'Content-Type': 'multipart/form-data'}
                });

                if (this.activeChatId === chatId) {
                    // Оптимистично добавляем готовое сообщение (с текстом и файлами сразу)
                    if (!this.messages.some(m => m.id === response.data.id)) {
                        this.messages.push(response.data);
                        this.groupsMessages = this.groupedMessages(this.messages);
                    }
                }

                return response.data;
            } catch (error) {
                alert('Ошибка отправки сообщения');
                console.error('Ошибка отправки сообщения:', error);
                throw error;
            }
        },

        //Метод пометки чата прочитанным
        async readChatMessages(chatId) {
            try {
                await axios.post(`/api/chats/${chatId}/read`);
                this.resetUnreadCount(chatId);
            } catch (error) {
                console.error('Ошибка прочтения чата:', error);
            }
        },

        // Очистить историю сообщений
        async clearChatAction(chatId) {
            try {
                await axios.post(`/api/chats/${chatId}/clear`);
                this.messages = []; // Мгновенно очищаем ленту сообщений на экране
                this.groupsMessages = [];
            } catch (error) {
                console.error('Ошибка очистки чата:', error);
                throw error;
            }
        },

        // Полностью удалить чат для всех участников
        async deleteChatAction(chatId) {
            try {
                await axios.delete(`/api/chats/${chatId}`);

                // Сбрасываем текущее активное состояние
                this.activeChatId = null;
                this.activeInterlocutor = null;
                this.messages = [];
                this.groupsMessages = [];
                // Обновляем список чатов
                await this.fetchChats();
            } catch (error) {
                console.error('Ошибка удаления чата:', error);
                throw error;
            }
        },

        incrementUnreadCount(chatId) {
            const chat = this.chatList.find(c => c.id === chatId);
            if (chat) {
                chat.unread_count = (chat.unread_count || 0) + 1;
            } else {
                this.fetchChats();
            }
        },

        resetUnreadCount(chatId) {
            const chat = this.chatList.find(c => c.id === chatId);
            if (chat) chat.unread_count = 0;
        }
    }
});
