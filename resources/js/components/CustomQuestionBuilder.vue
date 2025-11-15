<script setup lang="ts">
import { ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import { Card, CardContent } from '@/components/ui/card';
import { Trash2, Plus, GripVertical } from 'lucide-vue-next';

export interface CustomQuestion {
    id?: number;
    question_text: string;
    is_required: boolean;
    order: number;
    _destroy?: boolean;
}

const props = defineProps<{
    modelValue: CustomQuestion[];
}>();

const emit = defineEmits<{
    'update:modelValue': [value: CustomQuestion[]];
}>();

const questions = ref<CustomQuestion[]>([...props.modelValue]);

watch(
    () => props.modelValue,
    (newValue) => {
        questions.value = [...newValue];
    },
    { deep: true }
);

const updateQuestions = () => {
    emit('update:modelValue', questions.value);
};

const addQuestion = () => {
    questions.value.push({
        question_text: '',
        is_required: false,
        order: questions.value.length,
    });
    updateQuestions();
};

const removeQuestion = (index: number) => {
    const question = questions.value[index];
    if (question.id) {
        // Mark for deletion if it exists in database
        question._destroy = true;
    } else {
        // Remove from array if it's new
        questions.value.splice(index, 1);
    }
    updateQuestions();
};

const updateQuestion = (index: number, field: keyof CustomQuestion, value: any) => {
    questions.value[index][field] = value as never;
    updateQuestions();
};
</script>

<template>
    <div class="space-y-4">
        <div
            v-for="(question, index) in questions.filter((q) => !q._destroy)"
            :key="index"
            class="group"
        >
            <Card>
                <CardContent class="pt-6">
                    <div class="flex items-start gap-3">
                        <div class="mt-8 cursor-move text-gray-400">
                            <GripVertical class="h-5 w-5" />
                        </div>
                        <div class="flex-1 space-y-3">
                            <div>
                                <Label :for="`question-${index}`">
                                    Question {{ index + 1 }}
                                </Label>
                                <Input
                                    :id="`question-${index}`"
                                    :value="question.question_text"
                                    @input="
                                        updateQuestion(
                                            index,
                                            'question_text',
                                            ($event.target as HTMLInputElement).value
                                        )
                                    "
                                    placeholder="e.g., Why do you want to work for us?"
                                    class="mt-1"
                                />
                            </div>
                            <div class="flex items-center space-x-2">
                                <Checkbox
                                    :id="`required-${index}`"
                                    :checked="question.is_required"
                                    @update:checked="
                                        (checked) => updateQuestion(index, 'is_required', checked)
                                    "
                                />
                                <Label
                                    :for="`required-${index}`"
                                    class="cursor-pointer text-sm font-normal"
                                >
                                    Required
                                </Label>
                            </div>
                        </div>
                        <Button
                            type="button"
                            variant="ghost"
                            size="sm"
                            class="text-red-600 hover:text-red-700"
                            @click="removeQuestion(index)"
                        >
                            <Trash2 class="h-4 w-4" />
                        </Button>
                    </div>
                </CardContent>
            </Card>
        </div>

        <Button type="button" variant="outline" class="w-full" @click="addQuestion">
            <Plus class="mr-2 h-4 w-4" />
            Add Question
        </Button>
    </div>
</template>

