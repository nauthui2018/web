import * as React from "react";
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogClose,
} from "@/components/ui/dialog";
import {Button} from "@/components/ui/button";
import api from "@/lib/axios";
import { toast } from "sonner";

interface DeleteQuestionSetModalProps {
  isOpen: boolean;
  onClose: () => void;
  onDeleted: () => void;
  questionSetId: string;
  questionSetTitle: string;
}

export function DeleteQuestionSetModal({ isOpen, onClose, onDeleted, questionSetId, questionSetTitle }: DeleteQuestionSetModalProps) {
  const handleDelete = () => {
    api.delete(`/v1/management/tests/${questionSetId}`)
      .then(() => {
        toast.success("Question set deleted successfully", {
          description: `${questionSetTitle}`,
        })
        onDeleted();
        onClose();
      })
  };

  return (
    <Dialog open={isOpen} onOpenChange={onClose}>
      <DialogContent>
        <DialogHeader>
          <DialogTitle>Confirm Deletion</DialogTitle>
          <DialogDescription>
            Are you sure you want to delete this question set? This action cannot be undone.
            <br />
            <strong>{questionSetTitle}</strong>
          </DialogDescription>
        </DialogHeader>
        <DialogFooter>
          <DialogClose asChild>
            <Button variant="outline">Cancel</Button>
          </DialogClose>
          <DialogClose asChild>
            <Button variant="destructive" onClick={handleDelete}>
              Confirm
            </Button>
          </DialogClose>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  );
}