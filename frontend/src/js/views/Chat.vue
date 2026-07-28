<template>
  <!--div class="space-y-6">
  <div class="border-b border-slate-200 pb-5">
    <h1 class="text-2xl font-bold tracking-tight text-slate-900">Сообщения (Real-time мессенджер)</h1>
    <p>Коммуникационное ядро платформы, обеспечивающее мгновенную и безопасную связь между всеми участниками экосистемы.</p>
    <p></p>
    <ul class="mt-1 text-sm text-slate-500">
      <li>Зачем нужна: Для оперативного обсуждения деталей заказов, согласования этапов проектирования, пересылки рабочих документов и поддержания связи внутри проектных команд.</li>
      <li>Формат контента: Диалоги и групповые чаты, поддерживающие отправку зашифрованного текста со смайликами, встроенное воспроизведение видеороликов, галереи изображений и скачивание документов любых форматов.</li>
      <li>Логика работы: Работает на базе веб-сокетов Reverb и очередей RabbitMQ.</li>
    </ul>
  </div>

  <div class="max-w-3xl">

  </div>

</div-->
<!-- ВРЕМЕННАЯ ТЕСТОВАЯ ПЛАШКА ДЛЯ REDMI 9 -->
<!--  <div class="fixed top-2 right-2 z-50 bg-slate-900/90 text-white text-[10px] p-3 rounded-xl font-mono space-y-1 shadow-lg pointer-events-none">
    <div>📱 Window H: {{ windowHeight }}px</div>
    <div>👁️ Viewport H: {{ viewportHeight }}px</div>
    <div>⌨️ Клавиатура: {{ keyboardHeight }}px</div>
  </div>-->

  <div
      class="flex h-[90dvh] border border-slate-200 rounded-2xl bg-white shadow-xs font-sans">

    <!-- Левая колонка: Переключатель Чаты / Контакты -->
    <div
        class="w-full md:w-80 pt-5 absolute md:static inset-0 z-10 border-r border-slate-200 flex flex-col bg-slate-50/50"
        :class="chatStore.activeChatId ? 'hidden md:flex' : 'flex'">
      <!-- Вкладки управления -->
      <div class="p-4 border-b border-slate-200 bg-white space-y-3">

        <button
            @click="isGroupModalOpen = true"
            class="w-full mt-12 md:mt-0 mb-3 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-semibold text-xs py-2 px-4 rounded-xl transition-colors cursor-pointer flex items-center justify-center gap-1.5"
        >
          <span>➕</span> Создать группу
        </button>

        <!-- Кнопки переключения режимов -->
        <div class="flex rounded-lg bg-slate-100 p-0.5 border border-slate-200/50">
          <button
              @click="activeTab = 'chats'"
              class="flex-1 text-xs py-1.5 font-medium rounded-md transition-all cursor-pointer"
              :class="activeTab === 'chats' ? 'bg-white text-indigo-600 shadow-xs font-semibold' : 'text-slate-500 hover:text-slate-800'"
          >
            Диалоги
          </button>
          <button
              @click="activeTab = 'contacts'"
              class="flex-1 text-xs py-1.5 font-medium rounded-md transition-all cursor-pointer"
              :class="activeTab === 'contacts' ? 'bg-white text-indigo-600 shadow-xs font-semibold' : 'text-slate-500 hover:text-slate-800'"
          >
            Контакты
          </button>
        </div>

      </div>

      <!-- СПИСОК 1: Существующие диалоги (активные чаты) -->
      <div v-if="activeTab === 'chats'" class="flex-1 overflow-y-auto divide-y divide-slate-100 bg-white">
        <div v-if="chatStore.chatList.length === 0" class="p-8 text-center text-xs text-slate-400">
          У вас пока нет активных диалогов. Перейдите во вкладку "Контакты", чтобы начать общение.
        </div>

        <div
            v-for="chat in chatStore.chatList"
            :key="chat.id"
            @click="selectChat(chat.id, chat.users[0])"
            class="w-full text-left p-4 flex items-center gap-3 transition-colors cursor-pointer group"
            :class="chatStore.activeChatId === chat.id ? 'bg-indigo-50/70 border-l-4 border-indigo-600 pl-3' : 'hover:bg-slate-50'"
        >

          <!-- Круглая иконка пользователя (Аватарка с инициалом) -->
          <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-indigo-100 font-semibold text-sm text-indigo-700 uppercase tracking-wider group-hover:bg-indigo-200 transition-colors overflow-hidden border border-indigo-200/50"
               :class="chat.type === 'group' ? 'bg-emerald-100 text-emerald-700 border border-emerald-200/30' : 'bg-indigo-100 text-indigo-700 border border-indigo-200/30'"
          >
            <!-- 1. Если это ГРУППА и у нее есть кастомная аватарка -->
            <img v-if="chat.type === 'group' && chat.avatar_path"
                 :src="chat.avatar_path"
                 alt=""
                 class="h-full w-full object-cover rounded-full" />
            <!-- 2. Если это ЛИЧНЫЙ ЧАТ (peer) и у собеседника есть аватарка -->
            <img v-else-if="chat.type === 'peer' && chat.users[0]?.avatar_path"
                 :src="chat.users[0]?.avatar_path"
                 alt=""
                 class="h-full w-full object-cover rounded-full" />
            <!-- 3. Текстовые заглушки по умолчанию (если аватарок нет) -->
            <template v-else>
              <!-- Иконка для групп -->
              <span v-if="chat.type === 'group'">👥</span>
              <!-- Первая буква имени реального собеседника для личных чатов -->
              <span v-else>{{ chat.users[0]?.name?.charAt(0)  || 'U' }}</span>
            </template>
          </div>

          <!-- Имя собеседника и превью сообщения -->
          <div class="flex-1 min-w-0">

            <div class="flex items-center justify-between">
              <span class="font-semibold text-sm text-slate-800 truncate">
                {{  chat.type === 'group'? chat.title : chat.users[0]?.name }}
              </span>
              <!-- КРУЖОК НЕПРОЧИТАННЫХ СООБЩЕНИЙ -->
              <span v-if="chat.unread_count > 0"
                    class="flex h-5 min-w-5 items-center justify-center rounded-full bg-emerald-500 px-1 text-[10px] font-bold text-white shadow-xs">
                {{ chat.unread_count }}
              </span>
            </div>

            <!-- Динамическое превью последнего сообщения (Как в Telegram) -->
            <p class="text-xs truncate mt-0.5" :class="chat.unread_count > 0 ? 'text-slate-900 font-semibold' : 'text-slate-500'">
              <template v-if="chat.last_message">

                <!-- Если в сообщении есть прикрепленные файлы -->
                <template v-if="chat.last_message.attachments && chat.last_message.attachments.length > 0">
                  <span class="text-blue-600 font-medium">
                    <template v-if="chat.last_message.attachments[0].file_type === 'image'">📷 Фотография</template>
                    <template v-if="chat.last_message.attachments[0].file_type === 'video'">🎥 Видеоролик</template>
                    <template v-if="chat.last_message.attachments[0].file_type === 'file'">📎 Файл: {{ chat.last_message.attachments[0].file_name }}</template>
                  </span>
                  <span v-if="chat.last_message.text" class="text-slate-400 ml-1">— {{ chat.last_message.text }}</span>
                </template>

                <!-- Если отправлен только чистый текст -->
                <template v-else>
                  {{ chat.last_message.text }}
                </template>

              </template>
              <template v-else>
                <span class="text-slate-400 italic">Нет сообщений...</span>
              </template>
            </p>

            <!--<p class="text-xs text-slate-500 truncate mt-0.5">Нажмите, чтобы открыть переписку...</p>-->
          </div>

        </div>
      </div>

      <!-- СПИСОК 2: Все пользователи системы (Контакты) -->
      <div v-if="activeTab === 'contacts'" class="flex-1 overflow-y-auto divide-y divide-slate-100 bg-white">
        <div v-if="chatStore.usersList.length === 0" class="p-8 text-center text-xs text-slate-400">
          Загрузка списка пользователей или контакты отсутствуют...
        </div>
        <button
            v-for="user in chatStore.usersList"
            :key="user.id"
            @click="createOrGetChat(user)"
            class="w-full text-left p-4 flex items-center justify-between hover:bg-slate-50 transition-colors cursor-pointer"
        >


          <div class="flex flex-row gap-2 min-w-0">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-indigo-100 font-semibold text-sm text-indigo-700 uppercase tracking-wider group-hover:bg-indigo-200 transition-colors overflow-hidden border border-indigo-200/50">
              <!-- Добавили класс rounded-full на саму картинку -->
              <img
                  v-if="user.avatar_path"
                  :src="user.avatar_path"
                  alt="Аватар"
                  class="h-full w-full object-cover rounded-full"
              />
              <template v-else>
                {{ authStore.user?.name ? authStore.user.name.charAt(0) : 'U' }}
              </template>
            </div>
            <div class="flex flex-col min-w-0">
              <span class="font-semibold text-sm text-slate-800 truncate">{{ user.name }}</span>
              <span class="text-xs text-slate-400 truncate">{{ user.email }}</span>
            </div>
          </div>

          <!-- Метка роли -->
          <span
              class="text-[10px] uppercase font-bold tracking-wider px-2 py-0.5 rounded-sm bg-slate-100 text-slate-500">
            {{ user.role }}
          </span>
        </button>
      </div>
    </div>


    <!-- Правая колонка: Окно переписки -->
    <div class="flex-1 flex flex-col bg-slate-50/30 max-w-full" :class="chatStore.activeChatId ? 'flex' : 'hidden md:flex'">

      <!-- Если чат выбран -->
      <template v-if="activeChat">

        <!-- Шапка чата -->
        <div class=" h-14 border-b border-slate-200 bg-white px-6 flex items-center justify-between shadow-2xs">

          <!-- Стрелочка назад в мобилке -->
          <button @click="chatStore.activeChatId = null"
                  class="md:hidden mr-2 p-1 text-slate-500 hover:text-slate-700 cursor-pointer">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
          </button>

          <div class="flex flex-row gap-2">
            <div class="flex items-center gap-3 min-w-0">

              <div class="h-9 w-9 rounded-full font-bold text-xs flex items-center justify-center uppercase shrink-0 overflow-hidden"
                   :class="activeChat.type === 'group' ? 'bg-emerald-100 text-emerald-700' : 'bg-indigo-100 text-indigo-700'">

                <!-- Сценарий 1: Это группа и у нее есть аватар -->
                <img v-if="activeChat.type === 'group' && activeChat.avatar_path" :src="activeChat.avatar_path" alt="" class="h-full w-full object-cover" />

                <!-- Сценарий 2: Это личный чат и у собеседника есть аватар -->
                <img v-else-if="activeChat.type === 'peer' && activeChat.users[0]?.avatar_path" :src="activeChat.users[0]?.avatar_path" alt="" class="h-full w-full object-cover" />

                <!-- Сценарий 3: Заглушки, если картинок нет -->
                <template v-else>
                  <span v-if="activeChat.type === 'group'">👥</span>
                  <span v-else>{{ activeChat?.name?.charAt(0) || 'U' }}</span>
                </template>

              </div>

              <!-- Название и статус -->
              <div class="min-w-0">
                <h3 class="font-bold text-slate-800 text-xs truncate">
                  {{ activeChat?.type === 'group' ? activeChat.title : chatStore.activeInterlocutor?.name }}
                </h3>
                <p class="text-[10px] text-slate-400 truncate">
                  <!-- Если группа — пишем число участников -->
                  <span v-if="activeChat?.type === 'group'">
                  {{ activeChat.users_count || activeChat.users?.length || 0 }} участников
                </span>
                  <span v-else-if="isOnline(chatStore.activeInterlocutor?.id)" class="text-emerald-500 font-medium">в сети</span>
                  <span v-else>был(а) недавно</span>
                </p>
              </div>
            </div>

            <button
                v-if="activeChat?.type === 'group'"
                @click="isMembersModalOpen = true"
                type="button"
                class="p-2 text-slate-400 hover:text-indigo-600 hover:bg-slate-50 rounded-xl transition-colors cursor-pointer min-w-[36px] min-h-[36px] flex items-center justify-center"
                title="Посмотреть участников"
            >
              <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </button>
          </div>



          <!-- КНОПКИ УПРАВЛЕНИЯ ЧАТОМ -->
          <div class="flex items-center gap-1">
            <!-- Кнопка 1: Очистить диалог (Кисточка) -->
            <button
                v-if="activeChat?.type === 'peer' || (activeChat?.type === 'group' && amIAdmin)"
                @click="clearChat"
                title="Очистить диалог"
                type="button"
                class="p-2 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 active:bg-indigo-100 rounded-xl transition-colors cursor-pointer min-w-[36px] min-h-[36px] flex items-center justify-center"
            >
              <!-- Иконка: Кисть художественная/малярная -->
              <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 13h6m-6-4h6m-6 8h3" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 13a4 4 0 01-4 4H5" />
              </svg>
            </button>

            <!-- Кнопка 2: Удалить чат (Красное мусорное ведро) -->
            <button
                v-if="activeChat?.type === 'peer' || (activeChat?.type === 'group' && amIAdmin)"
                @click="deleteChat"
                title="Удалить чат"
                type="button"
                class="p-2 text-rose-500 hover:text-rose-700 hover:bg-rose-50 active:bg-rose-100 rounded-xl transition-colors cursor-pointer min-w-[36px] min-h-[36px] flex items-center justify-center"
            >
              <!-- Иконка: Мусорный бак (Корзина) -->
              <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
              </svg>
            </button>
          </div>

          <!--          <div class="flex items-center gap-2">
                      <button
                          @click="clearChat"
                          title="Очистить диалог"
                          class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition-colors cursor-pointer text-xs font-medium"
                      >
                        Очистить
                      </button>
                      <button
                          @click="deleteChat"
                          title="Удалить чат"
                          class="p-2 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded-lg transition-colors cursor-pointer text-xs font-medium"
                      >
                        Удалить
                      </button>

                    </div>-->

        </div>

        <!-- Лента сообщений -->
        <div
            ref="messageContainer"
            @scroll="handleScroll"
            :class="[
                'flex-1 flex flex-col gap-4 p-4 md:p-6 overflow-y-auto',
                isContextLoading ? 'opacity-30 pointer-events-none' : 'opacity-100'
                ]">

          <!-- Индикатор загрузки старых сообщений сверху -->
          <div v-if="isLoading" class="text-center text-xs text-gray-400 py-2">
            Загрузка истории...
          </div>

          <div class="flex-1 min-h-0"></div>
          <!--<div class="flex-1"></div>-->

          <div v-for="(dayMessages, date) in  chatStore.groupsMessages" :key="date" class="flex flex-col gap-4">

            <!-- Красивая плашка даты посередине экрана -->
            <div class="flex justify-center my-2">
                <span class="bg-gray-200 text-gray-600 text-xs px-3 py-1 rounded-full shadow-sm">
                  {{ date }}
                </span>
            </div>

            <div
                v-for="(msg, index) in dayMessages"
                :id="`msg-${msg.id}`"
                :key="msg.id"
                @click="handleMessageClick($event, msg)"
                class="flex flex-col max-w-[85%] md:max-w-[70%] group transition-transform duration-300"
                :class="[
                isMyMessage(msg.user_id) ? 'ml-auto items-end' : 'items-start',
                index === 0 ? 'mt-auto' : '',
                highlightedMessageId === msg.id ? 'animate-pulse-highlight' : ''
              ]"
            >
              <!-- Имя отправителя (для чужих) -->
              <span v-if="!isMyMessage(msg.user_id)" class="text-xs text-slate-400 font-medium ml-2 mb-1">
                {{ msg.user?.name }}
              </span>

              <!-- Контейнер: Облачко + Кнопки действий-->
              <div class="message-bubble flex items-center gap-2 max-w-full relative transition-all" :class="isMyMessage(msg.user_id) ? 'flex-row-reverse' : 'flex-row'">

                <!-- Облачко сообщения -->
                <div
                    class="px-4 py-2 text-sm shadow-2xs leading-relaxed break-words w-fit max-w-full relative"
                    :class="isMyMessage(msg.user_id)
                  ? 'bg-indigo-600 text-white rounded-2xl rounded-br-none pl-4 pr-4 pb-4'
                  : 'bg-white text-slate-800 border border-slate-100 rounded-2xl rounded-bl-none pl-4 pr-4 pb-4'"
                >

                  <!-- Визуализация ПЕРЕСЛАННОГО сообщения (ДОБАВИТЬ ЭТОТ БЛОК) -->
                  <!-- Предполагается, что в msg есть поле forwarded_from с данными автора -->
                  <div
                      v-if="msg.is_forwarded"
                      class="mb-1.5 flex flex-col text-[11px] border-l-2 pl-2 bg-black/3 py-0.5 rounded-r-md max-w-full"
                      :class="isMyMessage(msg.user_id) ? 'border-indigo-300 text-indigo-200' : 'border-slate-400 text-slate-400'"
                  >
                    <span class="font-bold flex items-center gap-1">
                      <!-- Иконка стрелочки пересылки -->
                      <svg class="h-3 w-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                      </svg>
                      Пересланное сообщение
                    </span>

                  </div>



                  <!-- ================= БЛОК ЦИТАТЫ ВНУТРИ ОБЛАЧКА ================= -->
                  <!-- Если у сообщения есть parent_id, выводим плашку-ссылку -->
                  <div
                      v-if="msg.parent"
                      @click="scrollToMessage(msg.parent.id)"
                      class="mb-1.5 p-2 rounded-lg text-xs border-l-2 bg-black/5 cursor-pointer hover:bg-black/10 transition-colors flex flex-col min-w-[120px] max-w-full"
                      :class="isMyMessage(msg.user_id) ? 'border-white/60 text-indigo-100' : 'border-indigo-500 text-slate-500'"
                  >
                    <span class="font-semibold" :class="isMyMessage(msg.user_id) ? 'text-white' : 'text-indigo-600'">
                      {{ msg.parent.user.name}}
                    </span>
                    <span class="truncate opacity-90">
                        {{msg.parent.text}}
                    </span>
                  </div>

                  <!-- Вывод текста -->
                  <span v-if="msg.text" class="block whitespace-pre-wrap">{{ msg.text }}</span>

                  <!-- Вывод вложений (attachments) -->
                  <div v-if="msg.attachments && msg.attachments.length > 0" class="space-y-2 mt-2">
                    <div v-if="msg.attachments && msg.attachments.length > 0" v-viewer class="space-y-2 mt-2">
                      <div v-for="file in msg.attachments" :key="file.id">
                        <!-- Если файл — КАРТИНКА -->
                        <div v-if="file.file_type === 'image'" class="max-w-xs">
                          <!-- УБРАЛИ @click, плагин сам обработает нажатие -->
                          <img
                              :src="getFileUrl(file.file_path)"
                              alt="Изображение"
                              class="rounded-lg max-h-60 w-full object-cover cursor-pointer hover:opacity-90 transition-opacity border border-slate-100/50"
                          />
                        </div>
                        <!-- Если файл — ВИДЕО -->
                        <div v-else-if="file.file_type === 'video'" class="max-w-xs sm:max-w-sm my-1 relative group">
                          <video
                              :src="getFileUrl(file.file_path)"
                              controls
                              playsinline
                              preload="metadata"
                              class="rounded-xl max-h-64 w-full object-contain bg-black border border-slate-100/10 shadow-sm"
                          >
                            Ваш браузер не поддерживает воспроизведение видео.
                          </video>

                          <!-- Кнопка развертывания, которая появляется при наведении на видео -->
                          <button
                              type="button"
                              @click="openVideoModal(getFileUrl(file.file_path))"
                              class="absolute top-2 right-2 p-1.5 rounded-lg bg-black/60 text-white opacity-0 group-hover:opacity-100 transition-opacity cursor-pointer hover:bg-black/80"
                              title="Развернуть на весь экран"
                          >
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                              <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3.75v4.5m0-4.5h4.5m-4.5 0L9 9M3.75 20.25v-4.5m0 4.5h4.5m-4.5 0L9 15M20.25 3.75v4.5m0-4.5h-4.5m4.5 0L15 9m5.25 11.25v-4.5m0 4.5h-4.5m4.5 0l-6-6" />
                            </svg>
                          </button>
                        </div>
                        <!-- Если файл — ДОКУМЕНТ -->
                        <div v-else class="flex items-center gap-2 py-1.5 px-2 rounded-xl bg-black/5 max-w-xs">
                          <svg class="h-7 w-7 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                          </svg>
                          <div class="flex flex-col min-w-0 flex-1">
                      <span class="font-medium text-xs truncate" :class="isMyMessage(msg.user_id) ? 'text-white' : 'text-slate-800'">
                        {{ file.file_name }}
                      </span>
                            <a
                                target="_blank"
                                download="true"
                                :href="getFileUrl(file.file_path)"
                                class="text-[10px] underline font-semibold mt-0.5 text-left cursor-pointer transition-colors"
                                :class="isMyMessage(msg.user_id) ? 'text-indigo-200 hover:text-white' : 'text-indigo-600 hover:text-indigo-800'"
                            >
                              Скачать
                            </a>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- СЛУЖЕБНЫЙ БЛОК: Время, Изменено, Галочки (Позиционируется абсолютно внизу справа облачка) -->
                  <div class="flex right-2.5 justify-end gap-1 text-[10px]" :class="isMyMessage(msg.user_id) ? 'text-indigo-200' : 'text-slate-400'">

                    <!-- Метка "изм." -->
                    <span v-if="isEdited(msg)" class="font-medium italic opacity-80">изм.</span>

                    <!-- Время отправки -->
                    <span>{{ formatTime(msg.created_at) }}</span>

                    <!-- ГАЛОЧКИ ДОСТАВКИ (Только для наших сообщений) -->
                    <div v-if="isMyMessage(msg.user_id)" class="ml-0.5 shrink-0">
                      <!-- Две галочки (Прочитано) -->
                      <svg v-if="msg.read_at" class="h-3.5 w-3.5 text-emerald-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7M5 18l4 4L19 12" />
                      </svg>
                      <!-- Одна галочка (Доставлено) -->
                      <svg v-else class="h-3.5 w-3.5 text-indigo-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                      </svg>
                    </div>
                  </div>

                  <!-- ================= БЛОК СЧЕТЧИКОВ РЕАКЦИЙ ПОД СООБЩЕНИЕМ ================= -->
                  <div
                      v-if="msg.reactions && msg.reactions.length > 0"
                      class="flex flex-wrap gap-1 mt-1 max-w-full"
                      :class="isMyMessage(msg.user_id) ? 'justify-end' : 'justify-start'"
                  >
                    <button
                        v-for="group in msg.reactions"
                        :key="group.emoji"
                        @click.stop="addReaction(msg.id, group.emoji)"
                        type="button"
                        class="flex items-center gap-1.5 px-2 py-0.5 text-xs rounded-full border transition-all cursor-pointer select-none active:scale-95"
                        :class="[
                      group.isMy
                        ? 'bg-indigo-50 border-indigo-200 text-indigo-600 font-medium shadow-2xs'
                        : 'bg-white border-slate-100 text-slate-500 hover:border-slate-200 hover:bg-slate-50'
                    ]"
                    >
                      <!-- Сам эмодзи -->
                      <span>{{ group.emoji }}</span>

                      <!-- Количество (показываем, только если больше 1, чтобы интерфейс был чище) -->
                      <span v-if="group.count > 0" class="text-[10px]">{{ group.count }}</span>
                    </button>
                  </div>

                </div>

                <!-- ================= АДАПТИВНОЕ КОНТЕКСТНОЕ МЕНЮ ДЕЙСТВИЙ ================= -->
                <div
                    class="absolute bg-white border border-slate-100 rounded-xl shadow-lg p-1 transition-all duration-150 z-20"
                    :class="[
                  // Логика отображения по клику (работает везде: и ПК, и Мобилка)
                  activeMessageId === msg.id
                  ? 'opacity-100 visible scale-100'
                  : 'opacity-0 invisible scale-95',

                  // Выравнивание углов (свои — справа, чужие — слева)
                  isMyMessage(msg.user_id) ? 'right-2 origin-right' : 'left-2 origin-left',
                  // Выравнивание по центру высоты (чтобы не расталкивать сообщения по вертикали)
                  'top-1/2 -translate-y-1/2',
                  // Структура: на мобилке вертикальное меню, на ПК (md:) — аккуратный горизонтальный ряд
                  'flex flex-col w-40 md:flex-row md:w-auto md:gap-0.5'
                ]"
                >
                  <!-- БЛОК БЫСТРЫХ РЕАКЦИЙ КОНТЕКСТНОЕ МЕНЮ-->
                  <div class="flex justify-around p-1.5 border-b border-slate-100 md:border-b-0 md:border-r border-slate-100 text-base gap-1.5 relative">
                    <button v-for="emoji in quickEmojis" :key="emoji" @click.stop="addReaction(msg.id, emoji)" class="hover:scale-125 transition-transform cursor-pointer">{{ emoji }}</button>
                    <button @click="toggleExtendedEmojis($event, msg.id)" type="button" class="text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-full w-6 h-6 flex items-center justify-center text-xs font-bold transition-colors cursor-pointer" title="Все реакции">➕</button>

                    <div
                        v-if="activeEmojiPickerId === msg.id"
                        @click.stop
                        class="absolute z-30 shadow-2xl rounded-xl overflow-hidden bg-white border border-slate-100 bottom-full mb-2"
                        :class="[
                      isMyMessage(msg.user_id) ? 'right-0' : 'left-0'
                    ]"
                    >
                      <EmojiPicker :picker-type="'popup'" :native="true" :theme="'light'" :hide-group-names="true" :disable-skin-tones="true" class="!w-[260px] !h-[300px] !shadow-none !border-none text-sm" @select="onSelectEmoji($event, msg.id)" />
                    </div>

                  </div>

                  <button @click.stop="replyMessage(msg)" type="button" class="flex items-center gap-2 p-1.5 text-slate-400 hover:text-slate-700 rounded-md hover:bg-slate-50 cursor-pointer text-xs md:text-sm" title="Ответить">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" /></svg>
                    <span class="md:hidden text-slate-600 font-medium">Ответить</span>
                  </button>

                  <!-- Кнопка: Копировать текст -->
                  <button
                      @click.stop="copyText(msg.text)"
                      type="button"
                      class="flex items-center gap-2 p-1.5 text-slate-400 hover:text-slate-700 rounded-md hover:bg-slate-50 cursor-pointer text-xs md:text-sm"
                      title="Копировать"
                  >
                    <!-- Иконка меняется при успешном копировании -->
                    <svg v-if="!isCopied" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <rect x="9" y="9" width="12" height="12" rx="2" stroke-linecap="round" stroke-linejoin="round"></rect>
                      <path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1" stroke-linecap="round" stroke-linejoin="round"></path>
                    </svg>
                    <svg v-else class="h-3.5 w-3.5 md:h-3.5 md:w-3.5 shrink-0 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>

                    <!-- Текст меняется при успешном копировании -->
                    <span class="md:hidden md:text-xs text-slate-600 font-medium">{{ isCopied ? 'Скопировано!' : 'Копировать' }}</span>
                  </button>


                  <!-- Кнопка: Переслать -->
                  <button @click.stop="forwardMessage(msg)" type="button" class="flex items-center gap-2 p-1.5 text-slate-400 hover:text-slate-700 rounded-md hover:bg-slate-50 cursor-pointer text-xs md:text-sm" title="Переслать">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                    <span class="md:hidden text-slate-600 font-medium">Переслать</span>
                  </button>

                  <!-- ДЕЙСТВИЯ ТОЛЬКО ДЛЯ МОИХ СООБЩЕНИЙ -->
                  <template v-if="isMyMessage(msg.user_id)">
                    <div class="h-px bg-slate-100 my-1 md:hidden"></div>

                    <!-- Редактировать -->
                    <button @click.stop="startEdit(msg)" type="button" class="flex items-center gap-2 p-1.5 text-slate-400 hover:text-blue-600 rounded-md hover:bg-slate-50 cursor-pointer text-xs md:text-sm" title="Редактировать">
                      <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                      <span class="md:hidden text-slate-600 font-medium">Изменить</span>
                    </button>

                    <!-- Удалить -->
                    <button @click.stop="deleteMessage(msg.id)" type="button" class="flex items-center gap-2 p-1.5 text-slate-400 hover:text-rose-600 rounded-md hover:bg-rose-50 cursor-pointer text-xs md:text-sm" title="Удалить для всех">
                      <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                      <span class="md:hidden text-rose-600 font-medium">Удалить</span>
                    </button>
                  </template>

                </div>
              </div>

            </div>

          </div>
        </div>


        <!-- Индикатор «Печатает...» в самом низу списка сообщений -->
        <div v-if="isTyping" class="typing-indicator">
          <i>{{ typingUser }} печатает...</i>
        </div>


        <!-- Подвал чата: Ввод текста и прикрепление файлов -->
        <div class="p-2 md:p-4 border-t border-slate-200 bg-white shadow-2xs relative">

          <!-- Окошко выбора Эмодзи -->
          <div v-if="showEmojiPicker" class="absolute bottom-20 right-4 z-50 shadow-xl rounded-xl overflow-hidden">
            <EmojiPicker :native="true" @select="onSelectEmoji" />
          </div>

          <!-- Скрытый системный инпут для файлов -->
          <input
              type="file"
              ref="fileInput"
              class="hidden"
              multiple
              @change="onFilesSelected"
          />

          <form @submit.prevent="sendMessage" class="flex flex-col gap-2">

            <!-- БЛОК ПРЕДПРОСМОТРА: Показывается только если прикреплены файлы -->
            <div v-if="selectedFiles.length > 0" class="flex flex-wrap gap-2 p-2 bg-slate-50 border border-slate-100 rounded-xl max-h-32 overflow-y-auto">
              <div
                  v-for="(file, index) in selectedFiles"
                  :key="index"
                  class="flex items-center gap-1.5 pl-2 pr-1 py-1 rounded-lg bg-white border border-slate-200 text-xs text-slate-700 max-w-[200px]"
              >
                <!-- Иконка скрепки для визуального обозначения файла -->
                <svg class="h-3.5 w-3.5 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                </svg>

                <span class="truncate flex-1 font-medium">{{ file.name }}</span>

                <!-- Кнопка удаления файла из списка (крестик) -->
                <button
                    type="button"
                    @click="removeFile(index)"
                    class="p-0.5 text-slate-400 hover:text-rose-500 rounded-md hover:bg-slate-100 cursor-pointer"
                    title="Убрать файл"
                >
                  <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>
              </div>
            </div>


            <!-- Индикатор режима редактирования -->
            <div v-if="editingMessageId" class="flex items-center justify-between px-3 py-1.5 bg-blue-50/70 border-l-4 border-blue-600 text-xs text-blue-800 rounded-r-xl mb-1">
              <div class="flex items-center gap-1.5 truncate">
                <svg class="h-3.5 w-3.5 text-blue-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                <span class="font-medium truncate">Редактирование сообщения...</span>
              </div>
              <button @click="cancelEdit" type="button" class="text-blue-500 hover:text-blue-700 font-semibold uppercase text-[10px] tracking-wider cursor-pointer">
                Отмена
              </button>
            </div>


            <!-- Контейнер нижней панели ввода сообщений -->
            <div v-if="replyingMessage" class="border-t border-slate-100 bg-white p-4">

              <!-- ПЛАШКА ОТВЕТА (Появляется только если есть replyingMessage) -->
              <div class="flex items-center justify-between gap-3 px-3 py-2 bg-slate-50 border-l-4 border-indigo-500 rounded-r-lg mb-2 text-xs transition-all animate-fade-in"
              >
                <div class="flex flex-col min-w-0">
                  <!-- Имя того, на чье сообщение отвечаем -->
                  <span class="font-semibold text-indigo-600 truncate">
                    Ответ пользователю {{ replyingMessage.user?.name || 'Собеседник' }}
                  </span>
                  <!-- Текст цитируемого сообщения -->
                  <span class="text-slate-500 truncate max-w-md">
                    {{ replyingMessage.text }}
                  </span>
                </div>

                <!-- Кнопка отмены ответа (крестик) -->
                <button
                    @click="cancelReply"
                    type="button"
                    class="p-1 text-slate-400 hover:text-slate-600 hover:bg-slate-200/50 rounded-full transition-colors cursor-pointer"
                    title="Отменить ответ"
                >
                  <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>
              </div>

            </div>

            <!-- Основная строка управления (Кнопки и Инпут) -->
            <div class="flex items-center gap-1 md:gap-3">

              <!-- Кнопка «Скрепка» -->
              <button
                  type="button"
                  @click="$refs.fileInput.click()"
                  class="text-slate-400 hover:text-indigo-600 hover:bg-slate-100 rounded-xl transition-colors cursor-pointer shrink-0"
                  title="Прикрепить файлы"
              >
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                </svg>
              </button>

              <!-- Кнопка «Смайлик» -->
              <button
                  type="button"
                  @click="showEmojiPicker = !showEmojiPicker"
                  class="text-slate-400 hover:text-indigo-600 hover:bg-slate-100 rounded-xl transition-colors cursor-pointer shrink-0"
                  title="Добавить эмодзи"
              >
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </button>

              <!-- Поле ввода текста -->
              <input
                  v-model="newMessageText"
                  type="text"
                  placeholder="Напишите сообщение..."
                  class="flex-1 border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 transition-shadow min-w-0"
              />

              <!-- Кнопка отправки «Самолетик» (Разблокируется, если есть текст ИЛИ прикреплен файл) -->
              <button
                  type="submit"
                  :disabled="!newMessageText && selectedFiles.length === 0"
                  class="bg-indigo-600 text-white p-2.5 rounded-xl hover:bg-indigo-500 disabled:opacity-40 disabled:pointer-events-none transition-colors cursor-pointer shrink-0"
                  title="Отправить сообщение"
              >
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3 21l19-9L3 3l3 9m0 0h6" />
                </svg>
              </button>

            </div>
          </form>
        </div>

      </template>

      <!-- Если чат НЕ выбран -->
      <div v-else class="flex-1 flex flex-col items-center justify-center p-8 text-center text-slate-400">
        <svg class="h-16 w-16 text-slate-300 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor"
             stroke-width="1.5">
          <path stroke-linecap="round" stroke-linejoin="round"
                d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
        </svg>
        <h3 class="mt-4 font-bold text-slate-700 text-base">Ваш мессенджер готов</h3>
        <p class="text-xs text-slate-400 max-w-xs mt-1">Выберите нужный диалог в левой колонке панели, чтобы начать
          переписку в реальном времени.</p>
      </div>

    </div>

  </div>

  <!-- МОДАЛЬНОЕ ОКНО ДЛЯ ПРОСМОТРА ВИДЕО НА ВЕСЬ ЭКРАН -->
  <div
      v-if="isVideoModalOpen"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/90 p-4 animate-fade-in"
      @click.self="closeVideoModal"
  >
    <!-- Кнопка закрытия (крестик) -->
    <button
        type="button"
        @click="closeVideoModal"
        class="absolute top-4 right-4 p-2 text-white/70 hover:text-white rounded-full bg-white/10 hover:bg-white/20 transition-colors cursor-pointer"
    >
      <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
      </svg>
    </button>

    <!-- Контейнер с видеоплеером -->
    <div class="max-w-4xl w-full max-h-[85vh] flex items-center justify-center">
      <video
          :src="currentModalVideoUrl"
          controls
          autoplay
          playsinline
          class="max-w-full max-h-[85vh] rounded-lg shadow-2xl object-contain bg-black"
      >
        Ваш браузер не поддерживает воспроизведение видео.
      </video>
    </div>
  </div>

  <!-- МОДАЛЬНОЕ ОКНО ДЛЯ ПРОСМОТРА АВАТАРА НА ВЕСЬ ЭКРАН -->
  <div
      v-if="isAvatarModalOpen && chatStore.activeInterlocutor?.avatar_path"
      class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/80 backdrop-blur-xs p-4"
      @click="isAvatarModalOpen = false"
  >
    <!-- Крестик для закрытия -->
    <button
        type="button"
        @click="isAvatarModalOpen = false"
        class="absolute top-4 right-4 p-2 text-white/70 hover:text-white rounded-full bg-white/10 hover:bg-white/20 transition-colors cursor-pointer"
    >
      <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
      </svg>
    </button>

    <!-- Крупное изображение (Ограничено по высоте экрана, чтобы не ломать верстку) -->
    <div class="max-w-md w-full max-h-[80vh] p-2 bg-white rounded-3xl shadow-2xl overflow-hidden">
      <img
          :src="chatStore.activeInterlocutor.avatar_path"
          alt="Большой аватар"
          class="w-full h-auto max-h-[75vh] object-cover rounded-2xl shadow-inner"
      />
    </div>
  </div>

  <!-- Bсплывающее уведомление для контекстного меню-->
  <transition
    enter-active-class="transition ease-out duration-200"
    enter-from-class="transform translate-y-4 opacity-0"
    enter-to-class="transform translate-y-0 opacity-100"
    leave-active-class="transition ease-in duration-150"
    leave-from-class="transform translate-y-0 opacity-100"
    leave-to-class="transform translate-y-4 opacity-0"
  >
    <div 
      v-if="toastContext.show" 
      class="fixed bottom-40 left-1/2 -translate-x-1/2 z-50 bg-slate-900/90 text-white text-xs font-medium px-4 py-2.5 rounded-full shadow-lg flex items-center gap-2 backdrop-blur-xs border border-slate-800"
    >
      <span>✨</span>
      <span>{{ toastContext.message }}</span>
    </div>
  </transition>

  <!-- Модальное окно: Переслать сообщение -->
  <div v-if="isForwardModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4">

    <!-- Задний затемняющий фон -->
    <div @click="closeForwardModal" class="absolute inset-0 bg-slate-900/50 backdrop-blur-xs"></div>

    <!-- Контентное окно -->
    <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-sm max-h-[80vh] flex flex-col overflow-hidden animate-fade-in">

      <!-- Шапка окна -->
      <div class="p-4 border-b border-slate-100 flex items-center justify-between">
        <h3 class="text-sm font-semibold text-slate-800">Переслать сообщение</h3>
        <button @click="closeForwardModal" class="text-slate-400 hover:text-slate-600 cursor-pointer">
          <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <!-- Текст пересылаемого сообщения (мини-превью) -->
      <div v-if="forwardingMessage" class="p-3 bg-slate-50 text-xs text-slate-500 border-l-4 border-indigo-500 mx-4 mt-3 rounded-r-lg truncate">
        {{ forwardingMessage.text }}
      </div>


      <!-- Список доступных чатов для пересылки -->
      <div class="flex-1 overflow-y-auto p-4 space-y-2">
        <!-- Предполагается, что у вас в сторе есть список всех чатов, например, chatStore.chats -->
        <div
            v-for="chat in chatStore.chatList"
            :key="chat.id"
            @click="submitForward(chat.id)"
            class="flex items-center gap-3 p-2 rounded-xl hover:bg-slate-50 cursor-pointer transition-colors active:bg-slate-100"
        >

          <!-- Аватар чата / собеседника -->
          <img
              v-if="chat.users[0]?.avatar_path"
              :src="chat.users[0].avatar_path"
              alt="Аватар"
              class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-xs font-semibold text-indigo-600 shrink-0"
          />
          <div v-else class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-xs font-semibold text-indigo-600 shrink-0">
            {{ chat.users[0].name?.substring(0, 2).toUpperCase() }}
          </div>

          <!-- Название чата -->
          <div class="flex-1 min-w-0">
            <p class="text-xs font-medium text-slate-700 truncate">{{chat.title ? chat.title : chat.users[0].name }}</p>
          </div>

          <!-- Стрелочка отправки -->
          <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7m0 0l-7 7m7-7H3" />
          </svg>
        </div>
      </div>

    </div>
  </div>

  <div v-if="isGroupModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4" @click.self="isGroupModalOpen = false">
    <div class="bg-white rounded-2xl w-full max-w-md flex flex-col shadow-2xl overflow-hidden">

      <div class="p-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
        <h3 class="font-bold text-slate-800 text-sm">👥 Создание новой группы</h3>
        <button @click="isGroupModalOpen = false" class="text-slate-400 hover:text-slate-600 text-sm cursor-pointer">✕</button>
      </div>

      <form @submit.prevent="submitCreateGroup" class="p-4 flex flex-col gap-4">
        <!-- Название группы -->
        <div class="flex flex-col gap-1">
          <label class="text-xs font-bold text-slate-500">Название группы</label>
          <input v-model="groupTitle" type="text" placeholder="Например: Разработчики" required class="border border-slate-200 rounded-xl px-3 py-2 text-xs focus:border-indigo-500 focus:outline-none" />
        </div>

        <!-- Выбор участников (Чекбоксы) -->
        <div class="flex flex-col gap-1">
          <label class="text-xs font-bold text-slate-500">Выберите участников</label>
          <div class="border border-slate-200 rounded-xl max-h-40 overflow-y-auto p-2 space-y-1.5">
            <!-- Замените contactsList на ваш массив всех пользователей системы -->
            <label v-for="user in chatStore.usersList" :key="user.id" class="flex items-center gap-2.5 p-1.5 hover:bg-slate-50 rounded-lg cursor-pointer text-xs">
              <input type="checkbox" :value="user.id" v-model="selectedUserIds" class="rounded text-indigo-600 focus:ring-indigo-500" />
              <span class="font-medium text-slate-700">{{ user.name }}</span>
            </label>
          </div>
        </div>

        <button type="submit" :disabled="!groupTitle || selectedUserIds.length === 0" class="w-full bg-indigo-600 hover:bg-indigo-500 disabled:opacity-40 text-white font-bold text-xs py-2.5 rounded-xl transition-colors cursor-pointer mt-2">
          Создать групповый чат
        </button>
      </form>

    </div>
  </div>

  <!-- МОДАЛЬНОЕ ОКНО: Список участников группы -->
  <div
      v-if="isMembersModalOpen && activeChat"
      class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4"
      @click.self="isMembersModalOpen = false"
  >
    <div class="bg-white rounded-2xl w-full max-w-sm max-h-[75vh] flex flex-col shadow-2xl overflow-hidden animate-fade-in">

      <!-- Шапка модалки -->
      <div class="p-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
        <div>
          <h3 class="font-bold text-slate-800 text-sm truncate max-w-[240px]">
            {{ activeChat.title }}
          </h3>
          <p class="text-[10px] text-slate-400 mt-0.5">Участники группы</p>
        </div>
        <button @click="isMembersModalOpen = false" class="text-slate-400 hover:text-slate-600 text-sm cursor-pointer p-1">✕</button>
      </div>

      <!-- Список участников -->
      <div class="flex-1 overflow-y-auto p-2 divide-y divide-slate-50 overscroll-contain">
        <div
            v-for="user in activeChat.users"
            :key="user.id"
            class="flex items-center justify-between p-2.5 rounded-xl hover:bg-slate-50 transition-colors"
        >
          <div class="flex items-center gap-2.5 min-w-0">
            <div class="h-8 w-8 rounded-full bg-indigo-50 font-bold text-[10px] text-indigo-600 flex items-center justify-center uppercase shrink-0 border border-indigo-100/20">
              <img v-if="user.avatar_path" :src="user.avatar_path" alt="" class="h-full w-full object-cover rounded-full" />
              <template v-else>{{ user.name?.charAt(0) || 'U' }}</template>
            </div>
            <div class="min-w-0">
              <p class="text-xs font-semibold text-slate-700 truncate">{{ user.name }}</p>
              <p class="text-[9px] text-slate-400 truncate">{{ user.email }}</p>
            </div>
          </div>

          <!-- Метка роли (Админ / Участник) -->
          <span
              class="text-[9px] font-bold px-2 py-0.5 rounded-md"
              :class="user.id === activeChat.creator_id ? 'bg-amber-50 text-amber-600 border border-amber-100' : 'bg-slate-100 text-slate-500'"
          >
            {{ user.id === activeChat.creator_id ? 'Админ' : 'Участник' }}
          </span>

          <button
              v-if="amIAdmin && user.id !== currentUserId"
              @click="kickUser(user.id)"
              class="text-[10px] text-rose-500 hover:bg-rose-50 px-2 py-0.5 rounded-md font-medium cursor-pointer transition-colors"
          >
            Удалить
          </button>

        </div>
      </div>

    </div>
  </div>
  <!-- МОДАЛКА 1: Выбор режима ОЧИСТКИ истории -->
  <div v-if="activeAction === 'clear'" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4">
    <div class="bg-white rounded-2xl w-full max-w-sm p-5 flex flex-col gap-4 shadow-2xl animate-fade-in">
      <h3 class="font-bold text-slate-800 text-sm">🧹 Очистить историю сообщений</h3>
      <!--   <p class="text-xs text-slate-500 leading-relaxed">Выберите, как вы хотите очистить переписку в этом чате:</p>   -->
      <div class="flex flex-col gap-2">
        <!--   <button @click="confirmClear(false)" class="w-full bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs py-2.5 rounded-xl cursor-pointer transition-colors">
          Очистить только у себя
        </button>  -->
        <!-- Кнопка "Для всех" активна всегда в peer-чате, а в группах — только для Админа -->
        <button v-if="activeChat?.type === 'peer' || amIAdmin" @click="confirmClear(true)" class="w-full bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-semibold text-xs py-2.5 rounded-xl cursor-pointer transition-colors">
          Очистить для всех
        </button>
      </div>
      <button @click="activeAction = null" class="text-xs text-slate-400 hover:text-slate-600 transition-colors mt-1 font-medium">Отмена</button>
    </div>
  </div>

  <!-- МОДАЛКА 2: Выбор режима УДАЛЕНИЯ чата -->
  <div v-if="activeAction === 'delete'" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4">
    <div class="bg-white rounded-2xl w-full max-w-sm p-5 flex flex-col gap-4 shadow-2xl animate-fade-in">
      <h3 class="font-bold text-rose-600 text-sm">🚨 Удаление чата</h3>
       <!--<p class="text-xs text-slate-500 leading-relaxed">Внимание! Выберите вариант удаления чата:</p>-->
      <div class="flex flex-col gap-2">
        <!--<button @click="confirmDelete(false)" class="w-full bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs py-2.5 rounded-xl cursor-pointer transition-colors">
          Удалить/Скрыть только у себя
        </button>-->
        <!-- Полное уничтожение группы/диалога из базы доступно только админу или в личной переписке -->
        <button v-if="activeChat?.type === 'peer' || amIAdmin" @click="confirmDelete(true)" class="w-full bg-rose-600 hover:bg-rose-500 text-white font-bold text-xs py-2.5 rounded-xl cursor-pointer transition-colors shadow-sm">
          Удалить чат для всех
        </button>
      </div>
      <button @click="activeAction = null" class="text-xs text-slate-400 hover:text-slate-600 transition-colors mt-1 font-medium">Отмена</button>
    </div>
  </div>

</template>

<script setup>
import {ref, onMounted, onUnmounted, nextTick, watch} from 'vue';
import {useAuthStore} from '../store/auth.js';
import {useChatStore} from '../store/chat.js';
import EmojiPicker from 'vue3-emoji-picker';
import { computed } from 'vue'
import {useToastStore} from "../store/toast.js";

const authStore = useAuthStore();
const chatStore = useChatStore();
const toastStore = useToastStore();

const activeTab = ref('chats');
const newMessageText = ref('');
const messageContainer = ref(null);
const showEmojiPicker = ref(false);
const fileInput = ref(null);
const selectedFiles = ref([]);
const isVideoModalOpen = ref(false);
const currentModalVideoUrl = ref('');
const editingMessageId = ref(null);
let currentUserId = authStore.user?.id || null;
const isAvatarModalOpen = ref(false);
const isLoading = ref(false);
const onlineUsers = ref([]);
const isTyping = ref(false);
const typingUser = ref(null);
let typingTimeout = null;


const isMyMessage = (id) => id === currentUserId;

const BACKEND_URL = axios.defaults.baseURL;
// Хранит ID сообщения, на которое нажали на мобилке
const activeMessageId = ref(null)

const isContextLoading = ref(false);
const highlightedMessageId = ref(null);

// Автоматически находит нужный чат в массиве по его ID
const activeChat = computed(() => {
  return chatStore.chatList.find(chat => chat.id === chatStore.activeChatId) || null;
});


const activeAction = ref(null); // Хранит текущее действие ('clear', 'delete' или null)

// Очистить сообщения
/*const clearChat = async () => {
  if (!confirm('Вы уверены, что хотите очистить историю?')) return;
  await chatStore.clearChatAction(chatStore.activeChatId);
};

// Полностью удалить чат
const deleteChat = async () => {
  if (!confirm('Вы уверены, что хотите безвозвратно удалить чат?')) return;
  window.Echo.leaveChannel(`chat.${chatStore.activeChatId}`);
  await chatStore.deleteChatAction(chatStore.activeChatId);
};*/
// При клике на иконку кисточки
const clearChat = () => {
  activeAction.value = 'clear';
};

// При клике на иконку красного ведра
const deleteChat = () => {
  activeAction.value = 'delete';

};

// Подтверждение очистки истории
const confirmClear = async (forAll) => {
  try {
    activeAction.value = null;
    isContextLoading.value = true;

    // Передаем флаг for_all в теле или query-параметрах запроса
    await axios.post(`/api/chats/${chatStore.activeChatId}/clear`, {
      data: { for_all: forAll }
    });

    // Если удалили только у себя, у нас массив очистится, а у других по сокетам останется
   // chatStore.activeChatMessages = [];
    chatStore.messages = [];
    chatStore.groupsMessages = [];
  } catch (error) {
    console.error('Ошибка очистки:', error);
  } finally { isContextLoading.value = false; }
};

// Подтверждение удаления чата
const confirmDelete = async (forAll) => {
  try {
    activeAction.value = null;
    isContextLoading.value = true;

    await chatStore.deleteChatAction(chatStore.activeChatId);
    //chatStore.chatList = chatStore.chatList.filter(chat => chat.id !== chatStore.activeChatId);

  } catch (error) {
    console.error('Ошибка удаления чата:', error);
  } finally {
    isContextLoading.value = false;
    //window.Echo.leaveChannel(`chat.${chatStore.activeChatId}`);
  }
};

const kickUser = async (targetUserId) => {
  if (!confirm('Вы уверены, что хотите удалить этого пользователя из группы?')) return;

  try {
    await axios.delete(`/api/chats/${activeChat.value.id}/users/${targetUserId}`);

    // Мгновенно убираем исключенного пользователя из локального стейта во Vue
    activeChat.value.users = activeChat.value.users.filter(u => u.id !== targetUserId);
  } catch (error) {
    console.error('Ошибка при удалении участника:', error);
  }
};


// Создание группы
const isGroupModalOpen = ref(false);
const groupTitle = ref('');
const selectedUserIds = ref([]); // Массив ID выбранных пользователей

// Переменная для открытия модалки участников группы
const isMembersModalOpen = ref(false);

const amIAdmin = computed(() => {
  if (!activeChat.value || activeChat.value.type !== 'group') return false;
  return activeChat.value.creator_id === authStore.user.id;
});

const submitCreateGroup = async () => {
  if (!groupTitle.value || selectedUserIds.value.length === 0) return;

  try {
    const response = await axios.post('/api/chats/group', {
      title: groupTitle.value,
      user_ids: selectedUserIds.value
    });

    // Добавляем созданную группу в общий список чатов Pinia стора
    chatStore.chatList.unshift(response.data.data);

    // Автоматически открываем только что созданную группу
    chatStore.activeChatId = response.data.data.id;

    // Сбрасываем форму
    isGroupModalOpen.value = false;
    groupTitle.value = '';
    selectedUserIds.value = [];


  } catch (error) {
    console.error('Ошибка создания группы:', error);
    alert('Не удалось создать группу');
  }
};

/*const windowHeight = ref(0);
const viewportHeight = ref(0);
const keyboardHeight = ref(0);

const checkSizes = () => {
  windowHeight.value = window.innerHeight;
  if (window.visualViewport) {
    viewportHeight.value = Math.round(window.visualViewport.height);
    keyboardHeight.value = window.innerHeight - Math.round(window.visualViewport.height);
  }
};*/

/*async function jumpToMessage(parentMessageId) {
  // 1. Проверяем, может сообщение УЖЕ есть на экране?
  const localElement = document.getElementById(`msg-${parentMessageId}`);

  if (localElement) {
    // Если есть, просто плавно скроллим к нему
    localElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
    triggerHighlight(parentMessageId);
    return;
  }

  // 2. Если сообщения нет, включаем плавное скрытие контента и запрашиваем контекст
  isContextLoading.value = true;

  try {
    const response = await axios.get(`/api/chats/${chatStore.activeChatId}/context/${parentMessageId}`);

    // Заменяем массив сообщений в сторе на новый контекст
    chatStore.messages = response.data.data;
    chatStore.groupsMessages = chatStore.groupedMessages(chatStore.messages);

    // Обновляем курсоры (теперь пагинация должна уметь ходить и вверх, и вниз)
    // nextPageUrl.value = ... (ссылка с prev_cursor)

    // 3. Ждем, пока Vue отрендерит новые зашифрованные сообщения в DOM
    nextTick(() => {
      const targetElement = document.getElementById(`msg-${parentMessageId}`);
      if (targetElement) {
        // Скроллим ровно по центру экрана
        targetElement.scrollIntoView({ behavior: 'auto', block: 'center' });
      }

      // Выключаем загрузку (контейнер плавно проявляется уже в нужной позиции скролла)
      isContextLoading.value = false;

      // Включаем красивую подсветку цели
      triggerHighlight(parentMessageId);
    });

  } catch (error) {
    console.error("Ошибка загрузки контекста:", error);
    isContextLoading.value = false;
  }
}

// Функция подсветки
function triggerHighlight(id) {
  highlightedMessageId.value = id;
  setTimeout(() => {
    highlightedMessageId.value = null; // Выключаем подсветку через 2 секунды
  }, 2000);
}*/







async function handleScroll() {
  const container = messageContainer.value;
  if (!container || isLoading.value || !chatStore.nextPageUrl) return;

  // Если доскроллили до верха
  if (container.scrollTop === 0) {
    isLoading.value = true;

    // 1. Запоминаем высоту контейнера ДО вставки новых сообщений
    const previousHeight = container.scrollHeight;
    //console.log(previousHeight);
    try {
      const response = await axios.get(chatStore.nextPageUrl);

      // 2. Вставляем старые сообщения в начало
      chatStore.messages = [...response.data.data, ...chatStore.messages];
      chatStore.groupsMessages = chatStore.groupedMessages(chatStore.messages);

      // Обновляем указатель на СЛЕДУЮЩИЙ (более старый) курсор
      chatStore.nextPageUrl = response.data.next_page_url;

      // 3. Ждем, пока Vue обновит DOM и отрисует новые блоки
      nextTick(() => {

        // Вычисляем, насколько увеличился контейнер сверху
        const heightDifference = container.scrollHeight - previousHeight;
        console.log(heightDifference);
        // Сдвигаем ползунок вниз ровно на высоту добавленного контента.
        // Для пользователя экран визуально останется неподвижным!
        container.scrollTop = heightDifference;
      });
    } catch (error) {
      console.error("Ошибка пагинации:", error);
    } finally {
      isLoading.value = false;
    }
  }
}



const scrollToMessage = (parentId) => {
  if (!parentId) return

  // Находим оригинальное сообщение в массиве для проверки типов
  const parentMsg = chatStore.messages.find(m => Number(m.id) === Number(parentId))
  if (!parentMsg) return

  // Находим главный HTML-контейнер сообщения по ID
  const element = document.getElementById(`msg-${parentId}`)
  
  if (element) {
    // 1. Плавно скроллим к оригинальному сообщению, центрируя его на экране
    element.scrollIntoView({ behavior: 'smooth', block: 'center' })
    
    // Классы мягкого акцента для ВСЕГО блока сообщения
    // scale-[1.03] идеален для крупных блоков, чтобы верстка не перекрывала соседние элементы
    const scaleClasses = ['scale-[1.1]', 'z-30', 'relative']

    // 2. Быстро и мягко увеличиваем весь блок
    element.classList.add('duration-200', 'transition-transform', ...scaleClasses)
    
    // 3. Через 500мс плавно возвращаем его в исходное состояние
    setTimeout(() => {
      element.classList.remove(...scaleClasses)
      element.classList.add('duration-500')
      
      // Полностью очищаем временные классы анимации после завершения
      setTimeout(() => {
        element.classList.remove('duration-500', 'duration-200', 'transition-transform')
      }, 500)
    }, 500)
  }
}


// Вспомогательная функция для поиска текста родительского сообщения прямо на фронтенде
/*const getParentMessageText = (parentId) => {
  //console.log(chatStore.messages);
  //console.log(parentId);
  const parentMsg = chatStore.messages.find(m => Number(m.id) ===  Number(parentId));
    //console.log(parentMsg);
  return parentMsg ? parentMsg.text : 'Сообщение удалено или недоступно'
}*/

// Вспомогательная функция для поиска имени автора цитаты
/*
const getParentMessageAuthor = (parentId) => {
  console.log(chatStore.messages);
  const parentMsg = chatStore.messages.find(m => Number(m.id) ===  Number(parentId))
  return parentMsg?.user?.name || 'Собеседник'
}
*/

// ================= БЛОК РЕАКЦИЙ =================

// Хранит ID сообщения, у которого открыт полноценный пикер
const activeEmojiPickerId = ref(null)

// Массив быстрых реакций оставляем для удобства (как в Telegram)
const quickEmojis = ['❤️', '👍', '🔥', '😂']


const toggleExtendedEmojis = (event, msgId) => {
  event.stopPropagation()
  activeEmojiPickerId.value = activeEmojiPickerId.value === msgId ? null : msgId
}

// Функция, которая срабатывает при выборе любого эмодзи из библиотеки
const onSelectEmojiContext = (emojiObject, msgId) => {
  // emojiObject.i содержит сам символ смайлика (например, '🎉')
  if (emojiObject && emojiObject.i) {
    addReaction(msgId, emojiObject.i)
  }
  // Закрываем панель после выбора
  activeEmojiPickerId.value = null
  activeMessageId.value = null
}

// ================= ФУНКЦИОНАЛ КОНТЕКСТННОГО МЕНЮ =================


const handleMessageClick = (event, msg) => {

  //if (!window.matchMedia('(pointer: coarse)').matches) return

  if (msg && msg.id) {
    event.stopPropagation() // Блокируем всплытие, чтобы глобальный клик не закрыл меню
    
    // Переключаем меню: если открыто это же — закрываем, если другое — открываем его
    activeMessageId.value = activeMessageId.value === msg.id ? null : msg.id
    
    // При переключении основного меню всегда закрываем открытый пикер эмодзи
    activeEmojiPickerId.value = null 
  }
}


const closeActionsMenu = () => {
  activeMessageId.value = null
}

// Функция закрытия меню при клике в любое внешнее место
const closeEmojiPicker = () => {
   activeEmojiPickerId.value = null
}

// ================= ФУНКЦИОНАЛ ДЕЙСТВИЙ =================

// 1. Поставить реакцию


// 1. Функция подсчета и группировки реакций для конкретного сообщения
/*const getGroupedReactions = (msgReactions) => {
  if (!msgReactions || !msgReactions.length) return []

  const groups = {}

  msgReactions.forEach(r => {
    if (!groups[r.emoji]) {
      groups[r.emoji] = {
        emoji: r.emoji,
        count: 0,
        isMy: false
      }
    }
    groups[r.emoji].count++
    
    // Проверяем, поставили ли эту реакцию именно вы
    if (isMyMessage(r.user_id)) {
      groups[r.emoji].isMy = true
    }
  })

  // Возвращаем массив, отсортированный по количеству реакций (сначала популярные)
  return Object.values(groups).sort((a, b) => b.count - a.count)
}*/


const addReaction = async (msgId, emoji) => {

  closeActionsMenu();
  closeEmojiPicker(); 

  const msg = chatStore.messages.find(m => Number(m.id) === Number(msgId))
  if (!msg) return

  if (!msg.reactions) msg.reactions = []
  
  const myExistingReactionIdx = msg.reactions.findIndex(
    r => r.emoji === emoji
    && isMyMessage(r.user_id)
  )

  // Временно запоминаем старые реакции на случай ошибки запроса
  const backupReactions = [...msg.reactions]

  if (myExistingReactionIdx !== -1) {
    msg.reactions.splice(myExistingReactionIdx, 1) // убираем локально
  } else {
    msg.reactions.push({ user_id: chatStore.currentUserId, emoji: emoji }) // добавляем локально
  }

  try {
    const response = await  axios.post(`/api/messages/${msgId}/reactions`, {
      emoji: emoji
    })

     console.log(response);

    if (response.data && response.data.reactions) {
      msg.reactions = response.data.reactions;
      
    }
  } catch (error) {
    console.error('Не удалось сохранить реакцию в БД:', error)
    // Если сервер упал или выдал ошибку, возвращаем всё как было
    msg.reactions = backupReactions
    alert('Ошибка при отправке реакции')
  }
  //console.log(msg.reactions);
}

// 2. Скопировать текст в буфер
const isCopied = ref(false)
// Состояние для всплывающего уведомления
const toastContext = ref({
  show: false,
  message: ''
})


const copyText = async (text) => {
  try {
    // Копируем текст в буфер обмена
    await navigator.clipboard.writeText(text)
    closeActionsMenu();
    // Включаем статус "Скопировано"
    isCopied.value = true;
    // Показываем всплывающее уведомление
    toastContext.value = {
      show: true,
      message: 'Текст скопирован в буфер обмена'
    }


    // Через 2.5 секунды возвращаем исходную иконку/текст
    setTimeout(() => {
      toastContext.value.show = false
      isCopied.value = false;
    }, 2500)

  } catch (err) {
    
     toastContext.value = {
      show: true,
      message: 'Не удалось скопировать текст'
    }
    setTimeout(() => { toast.value.show = false }, 2500)
    console.error('Не удалось скопировать текст: ', err);
    //alert('Ошибка при копировании');
  } 

  
}

// 3. Ответить на сообщение

// Хранит объект сообщения, на которое мы сейчас отвечаем
const replyingMessage = ref(null)

const replyMessage = (msg) => {
  console.log('Ответ на сообщение:', msg);
  replyingMessage.value = msg;
  // Здесь логика: запись msg в переменную replyToMessage, чтобы отобразить плашку над инпутом ввода
  closeActionsMenu();

   // Прогрессивное улучшение: автоматически ставим фокус в поле ввода текста, если у вас есть ref на него
  // nextTick(() => { inputRef.value?.focus() })
}

// Функция для отмены ответа (клик на крестик)
const cancelReply = () => {
  replyingMessage.value = null
}

// 4. Переслать сообщение
// Хранит сообщение, которое пользователь хочет переслать
const forwardingMessage = ref(null)

// Отвечает за видимость модального окна со списком чатов
const isForwardModalOpen = ref(false)

// Функция вызывается при клике на кнопку "Переслать" в контекстном меню
const forwardMessage = (msg) => {

  forwardingMessage.value = msg
  // Мгновенно закрываем контекстное меню сообщения
  closeActionsMenu();

  // Открываем модальное окно выбора чата
  isForwardModalOpen.value = true
}

// Функция для окончательной отправки сообщения в выбранный чат
const submitForward = async (targetChatId) => {
  if (!forwardingMessage.value) return

  try {
    const formData = new FormData()
    formData.append('chat_id', targetChatId)
    // Пересылаем текст оригинального сообщения
    if (forwardingMessage.value.text) {
      formData.append('text', forwardingMessage.value.text)
    }

    // Метка для бэкэнда (опционально), что это сообщение переслано
    formData.append('is_forwarded', 1);

     // Если у сообщения были файлы/вложения, их тоже можно переслать (зависит от бэка)
     if (forwardingMessage.value.attachments.length > 0) {
       formData.append('attachments', JSON.stringify(forwardingMessage.value.attachments));
     }

    // Вызываем ваш существующий экшен отправки
    await chatStore.sendMessageAction(targetChatId, formData);
    const targetChat = chatStore.chatList.find(c => c.id === targetChatId);
    console.log(targetChat.users[0].name);
    // Если переслали в текущий активный чат — сразу отображаем его в ленте
    if (chatStore.activeChatId === targetChatId) {
      scrollToBottom();
    }

    // Закрываем модальное окно и очищаем состояние
    closeForwardModal();

    // Показываем красивый Toast об успешной отправке (код тоста у нас уже есть)
    toastStore.add(`Уведомление`, 'Сообщение переслано ' + targetChat.users[0].name, 'info');

  } catch (error) {
    console.error('Не удалось переслать сообщение:', error)
  }
}

// Закрытие модального окна
const closeForwardModal = () => {
  isForwardModalOpen.value = false
  forwardingMessage.value = null
}

// ================= ФУНКЦИОНАЛ ЧАТА =================
// Эту функцию нужно повесить на @input или @keydown вашего текстового поля (textarea)
function sendTypingEvent() {

  if (!chatStore.activeChatId) return;

  // Отправляем «шепот» всем остальным участникам этого чата
  window.Echo.private(`chat.${chatStore.activeChatId}`)
      .whisper('typing', {
        id: authStore.user.id,
        name: authStore.user.name // передаем имя, чтобы красиво написать "Иван печатает..."
      });
}

// Выбор и открытие чата
const selectChat = async (id, user = null) => {

  if (chatStore.activeChatId) {
    window.Echo.leaveChannel(`chat.${chatStore.activeChatId}`);
  }

  chatStore.activeChatId = id;
  chatStore.activeInterlocutor = user;

  await chatStore.loadMessages(id);
  await chatStore.readChatMessages(id);
  scrollToBottom();

  window.Echo.private(`chat.${id}`)

     // 1. Слушаем отправку НОВЫХ сообщений
      .listen('MessageSent', (e) => {
        // Обновляем превью последнего сообщения в списке чатов слева
        const targetChat = chatStore.chatList.find(c => c.id === id);
        if (targetChat) {
          targetChat.last_message = e;
        }

        // Если этот чат сейчас открыт на экране — пушим в ленту
        if (chatStore.activeChatId === id) {
          if (!chatStore.messages.some(m => m.id === e.id)) {
            chatStore.messages.push(e);
            chatStore.groupsMessages = chatStore.groupedMessages(chatStore.messages);
          }
          scrollToBottom();
          chatStore.readChatMessages(id); // Шлем бэку статус "прочитано"
        } else {
          // Если чат в фоне — увеличиваем зеленый кружок счетчика
          if (targetChat) {
            targetChat.unread_count = (targetChat.unread_count || 0) + 1;
          }
        }
      })

      // 2. Слушаем РЕДАКТИРОВАНИЕ сообщений (Собеседник изменил текст)
      .listen('MessageUpdated', (e) => {
        // Находим измененное сообщение в нашей ленте и обновляем его контент
        const idx = chatStore.messages.findIndex(m => m.id === e.message.id);
        if (idx !== -1) {
          chatStore.messages[idx] = e.message;
          chatStore.groupsMessages = chatStore.groupedMessages(chatStore.messages);
        }

        // Также обновляем превью в левой панели, если это было самое последнее сообщение
        const targetChat = chatStore.chatList.find(c => c.id === id);
        if (targetChat && targetChat.last_message?.id === e.message.id) {
          targetChat.last_message = e.message;
        }
      })

      // 3. Слушаем УДАЛЕНИЕ сообщений (Собеседник удалил у себя для всех)
      .listen('MessageDeleted', (e) => {
        // Мгновенно стираем удаленное облачко с экрана
        chatStore.messages = chatStore.messages.filter(m => m.id !== e.messageId);
        chatStore.groupsMessages = chatStore.groupedMessages(chatStore.messages);
        
        if(chatStore.messages.length ===0){
            chatStore.fetchChats();
        }
      })

      .listen('MessageReactionToggle', (e) => {
      
        // Ищем, в каком сообщении изменились реакции
        const msg = chatStore.messages.find(m => Number(m.id) === Number(e.message_id))
        
        if (msg) {
          // Мгновенно обновляем локальный массив реакций в реальном времени
          msg.reactions = e.reactions
        }

      })

      .listen('ChatReadBroadcast', (e) => {
      
        // Собеседник прочитал чат. Значит, ВСЕ НАШИ сообщения в этом чате теперь прочитаны.
        // Пробегаемся по локальному массиву сообщений и проставляем read_at
        chatStore.messages.forEach(msg => {
          if (isMyMessage(msg.user_id) && !msg.read_at) {
            msg.read_at = e.read_at; // Устанавливаем время прочтения
          }
        });
        chatStore.groupsMessages = chatStore.groupedMessages(chatStore.messages);
        // Также обнуляем счетчик непрочитанных для этого чата в списке слева (на всякий случай)
        const targetChat = chatStore.chatList.find(c => c.id === id);
        if (targetChat) {
          targetChat.unread_count = 0;
        }
      })

      .listen('ClearChat', (e) => {
        chatStore.messages = [];
        chatStore.groupsMessages = [];
      })

      .listenForWhisper('typing', (e) => {
          typingUser.value = e.name;
          isTyping.value = true;

          // Если через 2 секунды новых сигналов «печатает» нет — скрываем плашку
          clearTimeout(typingTimeout);
          typingTimeout = setTimeout(() => {
            isTyping.value = false;
          }, 2000);
        });
 };


// Проверка, редактировалось ли сообщение
function isEdited(msg) {
  return msg.created_at !== msg.updated_at;
}

// Функция форматирования времени (из ISO в ЧЧ:ММ)
function formatTime(isoString) {
  if (!isoString) return '';
  const date = new Date(isoString);
  return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
}

// Начало редактирования: переносим текст в инпут
function startEdit(msg) {
  editingMessageId.value = msg.id;
  newMessageText.value = msg.text; // Подставляем текст в инпут
}

// Отмена редактирования
function cancelEdit() {
  editingMessageId.value = null;
  newMessageText.value = '';
}

// Функция для открытия видео на весь экран
function openVideoModal(url) {
  currentModalVideoUrl.value = url;
  isVideoModalOpen.value = true;
}

// Функция для закрытия модалки
function closeVideoModal() {
  isVideoModalOpen.value = false;
  currentModalVideoUrl.value = '';
}

// Функция для склеивания путей
function getFileUrl(path) {
  if (!path) return '';
  // Если бэкенд уже прислал полную ссылку (с http), возвращаем её
  if (path.startsWith('http://') || path.startsWith('https://')) {
    return path;
  }
  // Убираем лишние слэши при склеивании, если они есть
  const cleanPath = path.startsWith('/') ? path : `/${path}`;
  return `${BACKEND_URL}${cleanPath}`;
}

// Функция, которая вызывается при клике на эмодзи
function onSelectEmoji(emoji) {
  // Добавляем выбранный эмодзи в текущую позицию текста
  newMessageText.value += emoji.i;
  // Закрываем пикер после выбора (по желанию)
  showEmojiPicker.value = false;
}

// Функция для проверки в шаблоне Vue
function isOnline(userId) {
  return onlineUsers.value.includes(userId);
}

onMounted(() => {

  document.addEventListener('click', closeActionsMenu)
  chatStore.fetchChats();
  chatStore.fetchUsersList();

  const myId = authStore.user.id;

  window.Echo.private(`user.${myId}`)

      .listen('ChatDeleted', (e) => {
        chatStore.chatList = chatStore.chatList.filter(chat => chat.id !== e.chatId);
        // Если у этого пользователя прямо сейчас был ОТКРЫТ этот чат
        if (chatStore.activeChatId && chatStore.activeChatId === e.chatId) {
          chatStore.activeChatId = null; // Закрываем окно чата
          console.log('Этот чат был удален создателем')
          //alert('Этот чат был удален создателем');
        }
      })

      .listen('GroupChatCreated', (e) => {
        console.log(e);
        chatStore.fetchChats();
      })

      .listen('CreateChat', (e) => {
        console.log(e);
        chatStore.fetchChats();
  });

  window.Echo.join('online')
      // Метод здесь вызывается ОДИН раз при загрузке и возвращает список ВСЕХ, кто уже в сети
      .here((users) => {
        onlineUsers.value = users.map(user => user.id);
      })
      // Вызывается, когда КТО-ТО НОВЫЙ заходит в мессенджер
      .joining((user) => {
        if (!onlineUsers.value.includes(user.id)) {
          onlineUsers.value.push(user.id);
        }
      })
      // Вызывается, когда КТО-ТО ВЫХОДИТ (закрыл вкладку / пропал интернет)
      .leaving((user) => {
        onlineUsers.value = onlineUsers.value.filter(id => id !== user.id);
      })
      .error((error) => {
        console.error('Ошибка присутственного канала:', error);
      });

});

onUnmounted(() => {

  document.removeEventListener('click', closeActionsMenu)

  if (chatStore.activeChatId) {
    window.Echo.leaveChannel(`chat.${chatStore.activeChatId}`);
    chatStore.activeChatId = null;
  }

  const currentUserId = authStore.user.id;
  window.Echo.leave(`user.${currentUserId}`);

  console.log('Пользователь покинул чат. Полная очистка ресурсов...');

});

// Клик по контакту
const createOrGetChat = async (targetUser) => {
  try {
    const response = await axios.post('/api/chats', {user_id: targetUser.id});
    const chatId = response.data.chat_id;

    await chatStore.fetchChats();
    activeTab.value = 'chats';

    selectChat(chatId, targetUser);
  } catch (error) {
    alert('Не удалось запустить чат');
  }
};


// ФУНКЦИЯ УДАЛЕНИЯ СООБЩЕНИЯ
async function deleteMessage(messageId) {
  if (!confirm('Удалить это сообщение для всех?')) return;

  try {
    await axios.delete(`/api/messages/${messageId}`);
    // Удаляем локально с экрана
    chatStore.messages = chatStore.messages.filter(m => m.id !== messageId);
    chatStore.groupsMessages = chatStore.groupedMessages(chatStore.messages);

    if(chatStore.messages.length === 0){
        chatStore.fetchChats();    
    }

  } catch (error) {
    console.error('Не удалось удалить сообщение:', error);
  }
}

// Отправка сообщения
const sendMessage = async () => {

  if (!newMessageText.value.trim() && selectedFiles.value.length === 0) return;

  // ЕСЛИ МЫ РЕДАКТИРУЕМ СУЩЕСТВУЮЩЕЕ СООБЩЕНИЕ:
  if (editingMessageId.value) {
    try {
      const response = await axios.put(`/api/messages/${editingMessageId.value}`, {
        text: newMessageText.value,
      });

      // Обновляем сообщение локально на экране
      const idx = chatStore.messages.findIndex(m => m.id === editingMessageId.value);
      if (idx !== -1) chatStore.messages[idx] = response.data;
      chatStore.groupsMessages = chatStore.groupedMessages(chatStore.messages);


      cancelEdit(); // Сбрасываем режим редактирования
    } catch (error) {
      console.error('Не удалось отредактировать:', error);
    }
    return;
  }

  const formData = new FormData();
  formData.append('chat_id', chatStore.activeChatId);

  // Если мы отвечаем на сообщение, добавляем его ID в FormData для бэка
  if (replyingMessage.value) {
    formData.append('parent_id', replyingMessage.value.id);
  }

  if (newMessageText.value.trim()) {
    formData.append('text', newMessageText.value);
  }

  if (selectedFiles.value.length > 0) {
    selectedFiles.value.forEach(file => {
      formData.append('files[]', file);
    });
  }

  const message = await chatStore.sendMessageAction(chatStore.activeChatId, formData);

  if (!chatStore.messages.some(m => m.id === message.id)) {
    chatStore.messages.push(message);
    chatStore.groupsMessages = chatStore.groupedMessages(chatStore.messages);
  }

  newMessageText.value = '';
  selectedFiles.value = [];
  if (fileInput.value) fileInput.value.value = '';

// Сбрасываем плашку ответа на фронтенде, чтобы следующий ввод был обычным сообщением
  replyingMessage.value = null;

  chatStore.fetchChats();

  scrollToBottom();
};


// Хэндлер просто собирает файлы в массив для предпросмотра
function onFilesSelected(event) {
  const files = event.target.files;
  if (files.length > 0) {
    // Добавляем новые файлы к уже выбранным (чтобы можно было прикреплять несколько раз)
    selectedFiles.value.push(...Array.from(files));
  }
}

// Функция для удаления файла из списка перед отправкой, если пользователь передумал
function removeFile(index) {
  selectedFiles.value.splice(index, 1);
}





// Главная функция скролла
function scrollToBottom() {
  // nextTick ждет, пока Vue обновит HTML-дерево и вставит новые облачка в DOM
  nextTick(() => {
    if (messageContainer.value) {
      messageContainer.value.scrollTo({
        top: messageContainer.value.scrollHeight,
        behavior: 'smooth' // Плавный красивый скролл
      });
    }
  });
}

// Скроллим вниз, когда в массив chatStore.messages добавляются новые сообщения
/*watch(
    () => chatStore.messages,
    () => {
      scrollToBottom();
    },
    { deep: true } // deep нужен, так как мы следим за изменениями внутри массива
);*/

</script>

<style scoped>
.chat-container {
  padding: 32px;
}


@keyframes pulse-highlight {
  0% { transform: scale(1); filter: brightness(1); }
  50% { transform: scale(1.02); filter: brightness(1.2); }
  100% { transform: scale(1); filter: brightness(1); }
}
.animate-pulse-highlight {
  animation: pulse-highlight 1s ease-in-out 2; /* Мигнет 2 раза */
}
</style>