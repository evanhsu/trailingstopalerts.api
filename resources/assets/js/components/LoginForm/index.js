import React from 'react';
import PropTypes from 'prop-types';
import {
  Button,
  Paper,
  TextField,
  withStyles,
} from 'material-ui';

const styles = theme => ({
  button: {
    margin: 10,
  },
  form: {
    //
  },
  formLabel: {
    fontSize: 24,
  },
  textField: {
    marginLeft: theme.spacing.unit,
    marginRight: theme.spacing.unit,
    width: 200,
  },
  root: {
    display: 'flex',
    flexGrow: 1,
  },
});

const UsernameField = (props) => (
  <TextField
    required
    id="email"
    label="Email"
    className={props.className}
    margin="normal"
    onChange={props.onChange}
  />
);

const PasswordField = (props) => (
  <TextField
    id="password"
    label="Password"
    className={props.className}
    type="password"
    margin="normal"
    onChange={props.onChange}
  />
);

const LoginButton = (props) => (
  <Button variant="raised" className={props.className} onClick={props.onClick}>
    Login
  </Button>
);

const LoginForm = (props) => {
  const { classes } = props;

  return (
    <Paper className={classes.root}>
      <div className={classes.formLabel}>Login</div>
      <form className={classes.form} noValidate autoComplete="off">
        <UsernameField onChange={props.onFieldChange('email')} className={classes.textField}/>
        <PasswordField onChange={props.onFieldChange('password')} className={classes.textField}/>
        <LoginButton onClick={props.onLoginClick} className={classes.button}/>
      </form>
    </Paper>
  );
};

LoginForm.propTypes = {
  classes: PropTypes.object.isRequired,
  onLoginClick: PropTypes.func.isRequired,
  onFieldChange: PropTypes.func.isRequired,
};

export default withStyles(styles)(LoginForm);
