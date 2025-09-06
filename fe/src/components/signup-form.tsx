import { cn } from "@/lib/utils";
import Button from '@/components/Button'
import {Link, useNavigate} from "react-router-dom";
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import {useState} from "react";
import api from "@/lib/axios";

export function SignUpForm({
                             className,
                             ...props
                           }: React.ComponentPropsWithoutRef<"div">) {

  const [name, setName] = useState("");
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [confirmPassword, setConfirmPassword] = useState("");
  const [phone, setPhone] = useState("");
  const [role, setRole] = useState("user");
  const [isActive] = useState(true);
  const navigate = useNavigate();
  const [error, setError] = useState<string | null>(null);
  const handleSubmit = async (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault();
    setError(null);
    if (password !== confirmPassword) {
      setError("Passwords do not match");
      return;
    }
    try {
      const response = await api.post("/v1/auth/register", {
        name,
        email,
        password,
        password_confirmation: confirmPassword,
        phone,
        role,
        is_active: isActive,
      });
      if (response.data && response.data.success) {
        navigate("/login");
      } else {
        setError(response.data.message || "Registration failed");
      }
    } catch (err: any) {
      // Try to extract validation error message
      const msg = err.response?.data?.message || err.response?.data?.error?.details || "Registration failed";
      setError(msg);
    }
  };

  return (
      <div className={cn("flex flex-col gap-6", className)} {...props}>
        <div className="flex justify-center">
          <Link to="/">
            <img
                src="/logo.png"
                sizes="medium"
                alt="Company Logo"
                className="w-32 h-32 object-contain cursor-pointer"
            />
          </Link>
        </div>
        <Card>
          <CardHeader className="text-center">
            <CardTitle className="text-xl">Create an account</CardTitle>
            <CardDescription>Sign up with your details</CardDescription>
          </CardHeader>
          <CardContent>
            <form onSubmit={handleSubmit}>
              <div className="grid gap-6">
                <div className="grid gap-6">
                  <div className="grid gap-2">
                    <Label htmlFor="name">Name</Label>
                    <Input
                        id="name"
                        type="text"
                        placeholder="John Doe"
                        required
                        value={name}
                        onChange={e => setName(e.target.value)}
                    />
                  </div>
                  <div className="grid gap-2">
                    <Label htmlFor="email">Email</Label>
                    <Input
                        id="email"
                        type="email"
                        placeholder="m@example.com"
                        required
                        value={email}
                        onChange={e => setEmail(e.target.value)}
                    />
                  </div>
                  <div className="grid gap-2">
                    <Label htmlFor="password">Password</Label>
                    <Input id="password" type="password" required value={password} onChange={e => setPassword(e.target.value)} />
                  </div>
                  <div className="grid gap-2">
                    <Label htmlFor="confirm-password">Confirm Password</Label>
                    <Input id="confirm-password" type="password" required value={confirmPassword} onChange={e => setConfirmPassword(e.target.value)} />
                  </div>
                  {/* Add phone input */}
                  <div className="grid gap-2">
                    <Label htmlFor="phone">Phone</Label>
                    <Input id="phone" type="text" required value={phone} onChange={e => setPhone(e.target.value)} />
                  </div>
                  {error && (
                      <div className="text-red-500 text-sm text-center">
                        {error}
                      </div>
                  )}
                  <Button type="submit" className="w-full" >
                    Sign Up
                  </Button>
                </div>
                <div className="text-center text-sm">
                  Already have an account?{" "}
                  <Link
                      to="/login"
                      className="underline underline-offset-4 hover:text-primary"
                  >
                    Log in
                  </Link>
                </div>
              </div>
            </form>
          </CardContent>
        </Card>
        <div className="text-balance text-center text-xs text-muted-foreground [&_a]:underline [&_a]:underline-offset-4 [&_a]:hover:text-primary">
          By clicking continue, you agree to our{" "}
          <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a>.
        </div>
      </div>
  );
}