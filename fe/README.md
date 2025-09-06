# Test Platform Frontend

A modern React application for creating and taking multiple-choice tests, built with React, Tailwind CSS, and Lucide React icons.

## Features

### Admin Dashboard
- **Responsive sidebar navigation** with Dashboard, Question Sets, Users, and Settings
- **Top navbar** with search functionality and admin profile
- **Question Sets management** with a clean table interface
- **Add New Question Set modal** with dynamic question creation
- **Edit and Delete** functionality for question sets
- **Mobile-responsive** design with collapsible sidebar

### User Test Interface
- **Clean test-taking experience** with progress tracking
- **Countdown timer** with automatic submission
- **Question flagging** for review
- **Responsive design** that works on mobile and desktop
- **Progress bar** showing completion status
- **Navigation controls** with Previous/Next buttons
- **Submit functionality** with confirmation

## Technology Stack

- **React 18** - Modern React with hooks
- **React Router DOM** - Client-side routing
- **Tailwind CSS** - Utility-first CSS framework
- **Lucide React** - Beautiful, customizable icons
- **Create React App** - Development environment

## Getting Started

### Prerequisites

- Node.js (version 14 or higher)
- npm or yarn package manager

### Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd test-platform-fe
   ```

2. **Install dependencies**
   ```bash
   npm install
   ```

3. **Start the development server**
   ```bash
   npm start
   ```

4. **Open your browser**
   Navigate to `http://localhost:3000`

### Available Scripts

- `npm start` - Runs the app in development mode
- `npm build` - Builds the app for production
- `npm test` - Launches the test runner
- `npm eject` - Ejects from Create React App (not recommended)

## Project Structure

```
src/
├── pages/                    # Page-level components
│   ├── HomePage.js          # Landing page with navigation
│   ├── AdminDashboard.js    # Admin dashboard with sidebar and table
│   └── UserTest.js         # Test-taking interface
├── components/              # Reusable UI components
│   ├── Button.js           # Reusable button component
│   ├── Input.js            # Reusable input component
│   ├── Card.js             # Reusable card component
│   ├── Modal.js            # Reusable modal component
│   ├── AddQuestionSetModal.js # Modal for creating new question sets
│   └── index.js            # Component exports
├── App.js                  # Main app component with routing
├── index.js                # React entry point
└── index.css               # Global styles with Tailwind imports
```

## Usage

### Admin Dashboard (`/admin`)
1. Navigate to the admin dashboard
2. View existing question sets in the table
3. Click "Add New Question Set" to create a new test
4. Fill in the title and add questions with multiple choice options
5. Mark the correct answer for each question
6. Save the question set

### Taking a Test (`/test/:testId`)
1. Navigate to a test URL (e.g., `/test/1`)
2. Read the question and select your answer
3. Use the flag button to mark questions for review
4. Navigate between questions using Previous/Next buttons
5. Submit the test when finished or when time runs out

## Design Features

- **Modern UI/UX** with clean, minimalist design
- **Responsive layout** that adapts to different screen sizes
- **Accessible design** with proper contrast and keyboard navigation
- **Smooth animations** and transitions
- **Consistent color scheme** with blue as the primary color
- **Professional typography** with clear hierarchy
- **Reusable components** for consistent UI patterns

## Component Library

The application includes a set of reusable components:

### Button
```jsx
import { Button } from './components';

<Button variant="primary" size="md" onClick={handleClick}>
  Click me
</Button>
```

### Input
```jsx
import { Input } from './components';

<Input 
  label="Email" 
  placeholder="Enter your email"
  error="Invalid email"
/>
```

### Card
```jsx
import { Card } from './components';

<Card padding="md" shadow="lg">
  <h2>Card Title</h2>
  <p>Card content</p>
</Card>
```

### Modal
```jsx
import { Modal } from './components';

<Modal 
  isOpen={showModal} 
  onClose={closeModal}
  title="Modal Title"
  size="lg"
>
  <p>Modal content</p>
</Modal>
```

## Customization

### Colors
The primary color scheme can be customized in `tailwind.config.js`:

```javascript
theme: {
  extend: {
    colors: {
      primary: {
        50: '#eff6ff',
        500: '#3b82f6',
        600: '#2563eb',
        // ... more shades
      },
    },
  },
}
```

### Icons
The app uses Lucide React icons. You can replace any icon by importing a different one from the library.

## Future Enhancements

- User authentication and authorization
- Backend integration with API
- Test results and analytics
- Question bank management
- User management features
- Advanced question types (essay, file upload, etc.)
- Test scheduling and time limits
- Export functionality for results

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## License

This project is open source and available under the MIT License. 