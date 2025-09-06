import React, { useState, ChangeEvent } from 'react';
import { Plus, Trash2 } from 'lucide-react';
import Modal from './Modal';
import Button from './Button';
import Input from './Input';

export interface AddQuestionSetModalProps {
  onClose: () => void;
  onSave: (data: { title: string; questionCount: number }) => void;
}

interface Option {
  id: number;
  text: string;
  isCorrect: boolean;
}

interface Question {
  id: number;
  text: string;
  options: Option[];
}

const AddQuestionSetModal: React.FC<AddQuestionSetModalProps> = ({ onClose, onSave }) => {
  const [title, setTitle] = useState<string>('');
  const [questions, setQuestions] = useState<Question[]>([
    {
      id: 1,
      text: '',
      options: [
        { id: 1, text: '', isCorrect: false },
        { id: 2, text: '', isCorrect: false },
        { id: 3, text: '', isCorrect: false },
        { id: 4, text: '', isCorrect: false },
      ],
    },
  ]);

  const addQuestion = () => {
    const newQuestion: Question = {
      id: questions.length + 1,
      text: '',
      options: [
        { id: 1, text: '', isCorrect: false },
        { id: 2, text: '', isCorrect: false },
        { id: 3, text: '', isCorrect: false },
        { id: 4, text: '', isCorrect: false },
      ],
    };
    setQuestions([...questions, newQuestion]);
  };

  const removeQuestion = (questionId: number) => {
    if (questions.length > 1) {
      setQuestions(questions.filter((q) => q.id !== questionId));
    }
  };

  const updateQuestion = (questionId: number, text: string) => {
    setQuestions(
      questions.map((q) => (q.id === questionId ? { ...q, text } : q))
    );
  };

  const updateOption = (questionId: number, optionId: number, text: string) => {
    setQuestions(
      questions.map((q) =>
        q.id === questionId
          ? {
              ...q,
              options: q.options.map((opt) =>
                opt.id === optionId ? { ...opt, text } : opt
              ),
            }
          : q
      )
    );
  };

  const setCorrectOption = (questionId: number, optionId: number) => {
    setQuestions(
      questions.map((q) =>
        q.id === questionId
          ? {
              ...q,
              options: q.options.map((opt) => ({
                ...opt,
                isCorrect: opt.id === optionId,
              })),
            }
          : q
      )
    );
  };

  const handleSave = () => {
    if (!title.trim()) {
      alert('Please enter a title for the question set');
      return;
    }
    const validQuestions = questions.filter(
      (q) => q.text.trim() && q.options.some((opt) => opt.text.trim() && opt.isCorrect)
    );
    if (validQuestions.length === 0) {
      alert('Please add at least one complete question with a correct answer');
      return;
    }
    onSave({
      title: title.trim(),
      questionCount: validQuestions.length,
    });
  };

  return (
    <Modal isOpen={true} onClose={onClose} title="Add New Question Set" size="xl">
      <div className="p-6">
        {/* Title Input */}
        <div className="mb-6">
          <Input
            label="Set Title"
            value={title}
            onChange={(e: ChangeEvent<HTMLInputElement>) => setTitle(e.target.value)}
            placeholder="Enter question set title..."
          />
        </div>
        {/* Questions */}
        <div className="space-y-6">
          {questions.map((question, questionIndex) => (
            <div key={question.id} className="border border-gray-200 rounded-lg p-4">
              <div className="flex items-center justify-between mb-4">
                <h3 className="text-lg font-medium text-gray-900">
                  Question {questionIndex + 1}
                </h3>
                {questions.length > 1 && (
                  <Button
                    variant="ghost"
                    size="sm"
                    onClick={() => removeQuestion(question.id)}
                    className="text-red-600 hover:text-red-800"
                  >
                    <Trash2 className="w-4 h-4" />
                  </Button>
                )}
              </div>
              {/* Question Text */}
              <div className="mb-4">
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Question Text
                </label>
                <textarea
                  value={question.text}
                  onChange={(e) => updateQuestion(question.id, e.target.value)}
                  className="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  rows={3}
                  placeholder="Enter your question..."
                />
              </div>
              {/* Answer Options */}
              <div className="space-y-3">
                <label className="block text-sm font-medium text-gray-700">
                  Answer Options
                </label>
                {question.options.map((option, optionIndex) => (
                  <div key={option.id} className="flex items-center space-x-3">
                    <input
                      type="radio"
                      name={`question-${question.id}`}
                      checked={option.isCorrect}
                      onChange={() => setCorrectOption(question.id, option.id)}
                      className="w-4 h-4 text-blue-600 focus:ring-blue-500"
                    />
                    <input
                      type="text"
                      value={option.text}
                      onChange={(e) => updateOption(question.id, option.id, e.target.value)}
                      className="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                      placeholder={`Option ${String.fromCharCode(65 + optionIndex)}`}
                    />
                  </div>
                ))}
              </div>
            </div>
          ))}
        </div>
        {/* Add Question Button */}
        <Button variant="outline" onClick={addQuestion} className="mt-4">
          <Plus className="w-4 h-4 mr-2" />
          Add Question
        </Button>
      </div>
      {/* Footer */}
      <div className="flex items-center justify-end space-x-3 p-6 border-t border-gray-200">
        <Button variant="outline" onClick={onClose}>
          Cancel
        </Button>
        <Button variant="primary" onClick={handleSave}>
          Save Question Set
        </Button>
      </div>
    </Modal>
  );
};

export default AddQuestionSetModal; 