<template>
  <div class="input-group">
    <div class="input-group-prepend keyboard">
      <button type="button" class="btn" v-if="!showKeyboard" @click="toggleKeyboard">
        <i class="far fa-keyboard mr-2"></i>
        <i class="fas fa-caret-right"></i>
      </button>
      <button type="button" class="btn btn-default" v-show="showKeyboard"
        v-for="letter in ['ċ','ġ','ħ','ż']" :key="letter"
        @click="insert(letter)"
      >
        {{ letter }}
      </button>
    </div>
    <input type="search" name="s" class="form-control" autofocus="true"
      :placeholder="placeholder"
      @keydown.enter="$event.stopPropagation()"
      @keyup="updatePosition"
      @click="updatePosition"
      v-model="term"
      />
    <div class="input-group-append" v-if="showSubmit">
      <button type="submit" class="btn btn-primary">
        <i class="fas fa-search"></i>
      </button>
    </div>
  </div><!-- input-group -->
</template>

<script lang="ts">
import Vue from 'vue'

interface Data {
  term: string,
  showKeyboard: boolean,
  position: number
}

export default Vue.extend({
  props: {
    placeholder: String,
    showSubmit: Boolean
  },
  data (): Data {
    return {
      term: '', // updated by watch
      showKeyboard: false,
      position: 0
    }
  },
  methods: {
    toggleKeyboard (): void {
      this.showKeyboard = !this.showKeyboard
    },
    // Update cursor position on input
    updatePosition (e: Event): void {
      if (e.target) {
        this.position = (e.target as HTMLInputElement).selectionStart || 0
      }
    },
    insert (letter: string): void {
      this.term = this.term.slice(0, this.position) + letter + this.term.slice(this.position)
      this.position += letter.length
    }
  },
  watch: {
    '$route.query.s': {
      handler (): void {
        if (this.$route.name === 'lexemes') {
          this.term = this.$route.query.s as string || ''
        } else {
          this.term = ''
        }
      },
      immediate: true
    },
    term: {
      // inform parent that contents of input has changed
      handler (): void {
        this.$emit('update', this.term)
      }
    }
  }
})
</script>

<style lang="scss">
@import '@/assets/custom.scss';

.keyboard button {
  @extend .border;
  @extend .btn-outline-secondary;
}
</style>
