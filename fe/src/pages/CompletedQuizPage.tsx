import {useLocation, useNavigate} from "react-router-dom";
import {motion} from "framer-motion";
import Lottie from "lottie-react";

import successAnimation from "../assets/Success.json";
import failAnimation from "../assets/Failed.json";
import neutralAnimation from "../assets/Neutral.json";
import {Button} from "@/components/ui/button";
import React from "react";
import { QuizResultReviewModal } from "@/components/quiz-result-review-modal";

interface UserAnswer {
  questionId: number
  selectedOptionIds: number[]
  textAnswer?: string
  timeSpent: number
}

interface CompletionState {
  id: number,
  testId?: number,
  userId?: number,
  title: string,
  score: number,
  percentage: number,
  passingScore: number,
  totalQuestions: number,
  correctAnswers: number,
  durationTaken: number,
  passed: boolean,
  allowRetakes: boolean
  answers: UserAnswer[]
}

export default function CompletedPage() {
  const navigate = useNavigate();
  const [viewDetails, setViewDetails] = React.useState(false);
  const {state} = useLocation();
  const {
    id,
    testId,
    userId,
    title,
    score,
    percentage,
    passingScore,
    totalQuestions,
    correctAnswers,
    durationTaken,
    passed,
    allowRetakes,
    answers,
  } = (state as CompletionState) || {};

  const getMessage = () => {
    if (passed === true) return "Congratulations! You Passed!";
    if (passed === false) return "Sorry, You Failed!";
    return "Thank You for Taking the Test!";
  };

  const animationData =
      passed === true ? successAnimation : passed === false ? failAnimation : neutralAnimation;

  const formatMinutes = (sec: number) => `${Math.floor(sec / 60)} min ${Math.round(sec % 60)}s`;
  return (
      <div className="flex flex-col items-center justify-center p-6 mt-5">
        <motion.div
            initial={{opacity: 0, scale: 0.9}}
            animate={{opacity: 1, scale: 1}}
            transition={{duration: 0.5}}
            className="bg-white rounded-2xl shadow-2xl p-8 max-w-xl w-full text-center"
        >
          <div className="flex justify-center mb-4">
            <Lottie animationData={animationData} loop={true} className="w-[70%] h-[70%] mx-auto mb-4"/>
          </div>
          <h1 className="text-2xl font-bold mb-6 ">
            {getMessage()}
          </h1>

          <div className="text-left space-y-2 text-sm ">
            <p><strong>Quiz:</strong> {title}</p>
            <p><strong>Score:</strong> {score}</p>
            {passingScore != null && (
                <p><strong>Passing Score:</strong> {passingScore}</p>
            )}
            <p><strong>Correct answers:</strong> {correctAnswers}/{totalQuestions}</p>
            <p><strong>Time Taken:</strong> {formatMinutes(durationTaken * 60)}</p>
          </div>
          <div className="flex justify-center gap-4 mt-8">
            <Button onClick={() => navigate("/quiz")} variant="outline">
              Take Other Test
            </Button>
             <Button variant="outline" onClick={() => navigate(`/quiz/${testId}`)}>
                  Retake Test
              </Button>
            <Button onClick={() => setViewDetails(true)}>
                  View Details
            </Button>
            
          </div>
        </motion.div>

        <QuizResultReviewModal
          quizeResult={{
            id,
            testId: testId || 0,
            userId: userId || 0,
            title,
            score,
            percentage,
            passingScore,
            totalQuestions,
            correctAnswers,
            durationTaken,
            passed,
            allowRetakes,
            answers,
          }}
          isOpen={viewDetails}
          onClose={() => setViewDetails(false)}
        />  
      </div>
  );
}
