# AI Instructions for Laravel POS 2026 Upgrade Agents

This directory contains persistent AI instructions, skills, and workflows for each development agent role.

## Directory Structure

```
ai-instructions/
├── README.md                    # This file - AI instructions overview
├── architecture-agent/           # Architecture agent instructions
├── backend-agent/              # Backend development agent instructions  
├── frontend-agent/             # Frontend development agent instructions
├── testing-agent/              # Testing & QA agent instructions
├── database-agent/             # Database agent instructions
├── security-agent/            # Security agent instructions
├── devops-agent/              # DevOps agent instructions
├── documentation-agent/        # Documentation agent instructions
└── workflows/                # Common workflows and processes
    ├── tdd-process.md        # Test-Driven Development workflow
    ├── code-review.md        # Code review processes
    ├── debugging.md          # Debugging workflows
    └── security-review.md    # Security review processes
```

## AI Agent Configuration

Each agent instruction file should contain:

### 1. Agent Role Definition
- Primary responsibilities
- Scope of work
- Key deliverables

### 2. Technical Skills
- Required expertise areas
- Tool proficiencies
- Best practices

### 3. Workflow Processes
- Standard operating procedures
- Decision-making framework
- Quality standards

### 4. Communication Protocols
- Reporting structure
- Collaboration methods
- Escalation procedures

### 5. Task References
- Links to specific task documents
- Dependencies between tasks
- Success metrics

## Usage Instructions

When working with AI agents:

1. **Load Agent Instructions**: Reference the appropriate agent instruction file
2. **Follow Workflows**: Use the standardized processes in workflows/
3. **Update Instructions**: Modify instruction files when processes evolve
4. **Reference Tasks**: Always check task documents for specific requirements

## Persistence and Updates

- All agent instructions are version controlled in this repository
- Updates should be committed with descriptive messages
- Backward compatibility should be maintained when possible
- Review and update instruction files monthly

## Integration with AGENTS File

The main AGENTS file in the project root references these AI instructions:
- Task assignments reference specific instruction files
- Agent workflows reference common processes
- Quality standards are enforced through instruction guidelines

## Current AI Instructions Status

- [ ] architecture-agent/ - Create architecture agent instructions
- [ ] backend-agent/ - Create backend development instructions
- [ ] frontend-agent/ - Create frontend development instructions
- [ ] testing-agent/ - Create testing agent instructions
- [ ] database-agent/ - Create database agent instructions
- [ ] security-agent/ - Create security agent instructions
- [ ] devops-agent/ - Create DevOps agent instructions
- [ ] documentation-agent/ - Create documentation agent instructions
- [ ] workflows/ - Create common workflow instructions

## Next Steps

1. Create individual agent instruction files
2. Develop common workflow processes
3. Update main AGENTS file with references
4. Test agent instruction integration with task execution