import React, { useState, useEffect } from 'react';
import { useParams } from 'react-router-dom';
import { Flag, Clock, ChevronLeft, ChevronRight } from 'lucide-react';

interface Option {
  id: string;
  text: string;
}

interface Question {
  id: number;
  text: string;
  options: Option[];
}

interface TestData {
  id: string;
  title: string;
  questions: Question[];
}

const UserTest: React.FC = () => {
  const { testId } = useParams<{ testId: string }>();
  const [currentQuestionIndex, setCurrentQuestionIndex] = useState<number>(0);
  const [answers, setAnswers] = useState<Record<number, string>>({});
  const [flaggedQuestions, setFlaggedQuestions] = useState<Set<number>>(new Set());
  const [timeLeft, setTimeLeft] = useState<number>(15 * 60); // 15 minutes in seconds
  const [isSubmitted, setIsSubmitted] = useState<boolean>(false);

  // Mock test data
  const testData: TestData = {
    id: testId || '',
    title: "JavaScript Fundamentals Test",
    questions: [
      {
        id: 1,
        text: "What is the correct way to declare a variable in JavaScript?",
        options: [
          { id: 'A', text: "var myVariable = 5;" },
          { id: 'B', text: "variable myVariable = 5;" },
          { id: 'C', text: "v myVariable = 5;" },
          { id: 'D', text: "declare myVariable = 5;" }
        ]
      },
      {
        id: 2,
        text: "Which method is used to add an element to the end of an array?",
        options: [
          { id: 'A', text: "push()" },
          { id: 'B', text: "pop()" },
          { id: 'C', text: "shift()" },
          { id: 'D', text: "unshift()" }
        ]
      },
      {
        id: 3,
        text: "What does the 'typeof' operator return for an array?",
        options: [
          { id: 'A', text: "array" },
          { id: 'B', text: "object" },
          { id: 'C', text: "Array" },
          { id: 'D', text: "undefined" }
        ]
      },
      {
        id: 4,
        text: "How do you create a function in JavaScript?",
        options: [
          { id: 'A', text: "function myFunction()" },
          { id: 'B', text: "function:myFunction()" },
          { id: 'C', text: "function = myFunction()" },
          { id: 'D', text: "function => myFunction()" }
        ]
      },
      {
        id: 5,
        text: "What is the result of 2 + '2' in JavaScript?",
        options: [
          { id: 'A', text: "4" },
          { id: 'B', text: "22" },
          { id: 'C', text: "NaN" },
          { id: 'D', text: "Error" }
        ]
      }
    ]
  };

  const totalQuestions = testData.questions.length;
  const currentQuestion = testData.questions[currentQuestionIndex];
  const progress = Object.keys(answers).length;
  const isLastQuestion = currentQuestionIndex === totalQuestions - 1;

  // Timer effect
  useEffect(() => {
    if (timeLeft > 0 && !isSubmitted) {
      const timer = setInterval(() => {
        setTimeLeft(prev => prev - 1);
      }, 1000);
      return () => clearInterval(timer);
    } else if (timeLeft === 0 && !isSubmitted) {
      handleSubmitTest();
    }
  }, [timeLeft, isSubmitted]);

  const formatTime = (seconds: number) => {
    const mins = Math.floor(seconds / 60);
    const secs = seconds % 60;
    return `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
  };

  const handleAnswerSelect = (optionId: string) => {
    setAnswers(prev => ({
      ...prev,
      [currentQuestion.id]: optionId
    }));
  };

  const handleFlagQuestion = () => {
    setFlaggedQuestions(prev => {
      const newSet = new Set(prev);
      if (newSet.has(currentQuestion.id)) {
        newSet.delete(currentQuestion.id);
      } else {
        newSet.add(currentQuestion.id);
      }
      return newSet;
    });
  };

  const handlePrevious = () => {
    if (currentQuestionIndex > 0) {
      setCurrentQuestionIndex(prev => prev - 1);
    }
  };

  const handleNext = () => {
    if (currentQuestionIndex < totalQuestions - 1) {
      setCurrentQuestionIndex(prev => prev + 1);
    }
  };

  const handleSubmitTest = () => {
    setIsSubmitted(true);
    // Here you would typically send the answers to your backend
    console.log('Test submitted:', { answers, timeLeft });
  };

  if (isSubmitted) {
    return (
      <div className="min-h-screen bg-gray-50 flex items-center justify-center p-4">
        <div className="bg-white rounded-lg shadow-lg p-8 max-w-md w-full text-center">
          <h2 className="text-2xl font-bold text-gray-900 mb-4">Test Submitted!</h2>
          <p className="text-gray-600 mb-6">
            Your test has been successfully submitted. You answered {progress} out of {totalQuestions} questions.
          </p>
          <button
            onClick={() => window.location.href = '/'}
            className="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors"
          >
            Return to Home
          </button>
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gray-50">
      {/* Header */}
      <div className="bg-white shadow-sm border-b border-gray-200">
        <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex items-center justify-between h-16">
            <h1 className="text-lg font-semibold text-gray-900 truncate">
              {testData.title}
            </h1>
            <div className="flex items-center space-x-4">
              <div className="flex items-center space-x-2 text-gray-600">
                <Clock className="w-4 h-4" />
                <span className="font-medium">Time left: {formatTime(timeLeft)}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
      {/* Progress Bar */}
      <div className="bg-white border-b border-gray-200">
        <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
          <div className="flex items-center justify-between mb-2">
            <span className="text-sm font-medium text-gray-700">
              Progress: {progress} of {totalQuestions}
            </span>
            <span className="text-sm text-gray-500">
              {Math.round((progress / totalQuestions) * 100)}% Complete
            </span>
          </div>
          <div className="w-full bg-gray-200 rounded-full h-2">
            <div
              className="bg-blue-600 h-2 rounded-full transition-all duration-300"
              style={{ width: `${(progress / totalQuestions) * 100}%` }}
            />
          </div>
        </div>
      </div>
      {/* Main Content */}
      <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div className="bg-white rounded-lg shadow-lg p-6 md:p-8">
          {/* Question Header */}
          <div className="flex items-start justify-between mb-6">
            <div className="flex-1">
              <div className="text-sm text-gray-500 mb-2">
                Question {currentQuestionIndex + 1} of {totalQuestions}
              </div>
              <h2 className="text-xl font-semibold text-gray-900 leading-relaxed">
                {currentQuestion.text}
              </h2>
            </div>
            <button
              onClick={handleFlagQuestion}
              className={`ml-4 p-2 rounded-full transition-colors ${
                flaggedQuestions.has(currentQuestion.id)
                  ? 'text-red-600 bg-red-100'
                  : 'text-gray-400 hover:text-red-600 hover:bg-red-50'
              }`}
            >
              <Flag className="w-5 h-5" />
            </button>
          </div>
          {/* Answer Options */}
          <div className="space-y-3 mb-8">
            {currentQuestion.options.map((option) => (
              <button
                key={option.id}
                onClick={() => handleAnswerSelect(option.id)}
                className={`w-full p-4 text-left border-2 rounded-lg transition-all duration-200 ${
                  answers[currentQuestion.id] === option.id
                    ? 'border-blue-500 bg-blue-50'
                    : 'border-gray-200 hover:border-gray-300 hover:bg-gray-50'
                }`}
              >
                <div className="flex items-center">
                  <div className={`w-8 h-8 rounded-full border-2 flex items-center justify-center mr-4 font-semibold ${
                    answers[currentQuestion.id] === option.id
                      ? 'border-blue-500 bg-blue-500 text-white'
                      : 'border-gray-300 text-gray-600'
                  }`}>
                    {option.id}
                  </div>
                  <span className="text-gray-900">{option.text}</span>
                </div>
              </button>
            ))}
          </div>
          {/* Navigation */}
          <div className="flex items-center justify-between pt-6 border-t border-gray-200">
            <button
              onClick={handlePrevious}
              disabled={currentQuestionIndex === 0}
              className={`flex items-center px-4 py-2 rounded-md transition-colors ${
                currentQuestionIndex === 0
                  ? 'text-gray-400 cursor-not-allowed'
                  : 'text-gray-700 hover:bg-gray-100'
              }`}
            >
              <ChevronLeft className="w-4 h-4 mr-2" />
              Previous
            </button>
            <div className="flex space-x-3">
              {!isLastQuestion ? (
                <button
                  onClick={handleNext}
                  className="flex items-center px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors"
                >
                  Next
                  <ChevronRight className="w-4 h-4 ml-2" />
                </button>
              ) : (
                <button
                  onClick={handleSubmitTest}
                  className="px-6 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors"
                >
                  Submit Test
                </button>
              )}
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default UserTest; 