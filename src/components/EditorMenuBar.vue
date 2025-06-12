<template>
  <div class="toolbar">
    <button @click="editor?.chain().focus().toggleBold().run()" :class="{ active: editor?.isActive('bold') }"><b>B</b></button>
    <button @click="editor?.chain().focus().toggleItalic().run()" :class="{ active: editor?.isActive('italic') }"><i>I</i></button>
    <button @click="editor?.chain().focus().toggleUnderline().run()" :class="{ active: editor?.isActive('underline') }"><u>U</u></button>

    <!-- Color menu -->
    <select
      @change="editor?.chain().focus().setColor(($event.target as HTMLSelectElement).value).run()">
      <option value="black">⬛ Black</option>
      <option value="red">🟥 Red</option>
      <option value="green">🟩 Green</option>
      <option value="blue">🟦 Blue</option>
      <option value="orange">🟧 Orange</option>
      <option value="purple">🟪 Purple</option>
      <option value="pink">🩷 Pink</option>
      <option value="gray">⬜ Gray</option>
      <option value="brown">🟫 Brown</option>
      <option value="yellow">🟨 Yellow</option>
      <option value="cyan">🟦 Cyan</option>
      <option value="magenta">🟪 Magenta</option>
      <option value="white">⬜ White</option>
    </select>
    <button @click="editor?.chain().focus().setTextAlign('left').run()" :class="{ active: editor?.isActive({ textAlign: 'left' }) }"
      class="left-button"
    >
      <v-img
        src="/icons/align_left.svg"
        width="20"
        height="20"
      />
    </button>
    <button @click="editor?.chain().focus().setTextAlign('center').run()" :class="{ active: editor?.isActive({ textAlign: 'center' }) }"
      class="center-button"
    >
      <v-img
        src="/icons/align_center.svg"
        width="20"
        height="20"
      />
    </button>
    <button @click="editor?.chain().focus().setTextAlign('right').run()" :class="{ active: editor?.isActive({ textAlign: 'right' }) }"
      class="right-button"
    >
      <v-img
        src="/icons/align_right.svg"
        width="20"
        height="20"
      />
    </button>
    <button @click="editor?.chain().focus().setTextAlign('justify').run()" :class="{ active: editor?.isActive({ textAlign: 'justify' }) }"
      class="justify-button"
    >
      <v-img
        src="/icons/align_justify.svg"
        width="20"
        height="20"
      />
    </button>

    <!-- Undo and Redo-->
    <button @click="editor?.chain().focus().undo().run()" class="left-button">
      <v-img
        src="/icons/undo.svg"
        width="20"
        height="20"
      />
    </button>
    <button @click="editor?.chain().focus().redo().run()" class="justify-button">
      <v-img
        src="/icons/redo.svg"
        width="20"
        height="20"
      />
    </button>
  </div>
</template>

<script lang="ts">
export default {
  name: 'EditorMenuBar',
  props: { editor: Object },
};
</script>

<style scoped>
.toolbar {
  border: 1px solid #ccc;
  background: #f8f9fa;
  padding: 8px;
  margin-bottom: 5px;
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
}

button, select {
  margin-right: 5px;
  border: 2px solid var(--vt-c-gray-dark);
  border-radius: 5px;
  min-width: 2.5rem;
  padding: 5px;
  height: 30px;
  text-align: center;
  display: inline-flex;
  justify-content: center;
  align-items: center;
}

.left-button {
  border-radius: 5px 0 0 5px;
  margin-right: 0;
  border-right: 0;
}
.center-button {
  border-radius: 0;
  margin-right: 0;
  border-right: 0;
}
.right-button {
  border-radius: 0;
  margin-right: 0;
  border-right: 0;
}
.justify-button {
  border-radius: 0 5px 5px 0;
}

.active {
  background: #ddd;
}
</style>
