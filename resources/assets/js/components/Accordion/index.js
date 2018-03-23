import React from 'react';
import PropTypes from 'prop-types';
import ImmutablePropTypes from 'react-immutable-proptypes';
import Button from 'material-ui/Button';
import AddIcon from 'material-ui-icons/Add';
import { Map } from 'immutable';
import { Typography } from "material-ui";
import { withStyles } from 'material-ui/styles';
import StopAlertPanel from '../StopAlertPanel';

const styles = theme => ({
  root: {},
  accordionRoot: {
    flexGrow: 1,
  },
  button: {},
  heading: {
    display: 'flex',
    alignItems: 'center',
    paddingBottom: 15,
  },
  headingText: {
    fontSize: 40,
    paddingRight: 10,
  },
});

class Accordion extends React.Component {
  state = {
    expanded: null,
  };

  handleChange = panel => (event, expanded) => {
    this.setState({
      expanded: expanded ? panel : false,
    });
  };

  render() {
    const { classes } = this.props;
    const { expanded } = this.state;

    return (
      <div className={classes.root}>
        <div className={classes.heading}>
          <Typography className={classes.headingText}>Alerts</Typography>
          <Button mini variant="fab" color="secondary" aria-label="add" className={classes.button}>
            <AddIcon />
          </Button>
        </div>
        <div className={classes.accordionRoot}>
          {(this.props.stopAlerts.size > 0) && this.props.stopAlerts.map((stopAlert) => (
            <StopAlertPanel
              key={stopAlert.get('id').toString()}
              stopAlert={stopAlert}
              expanded={expanded === stopAlert.get('symbol')}
              onChange={this.handleChange(stopAlert.get('symbol'))}
            />
          ))}
        </div>
      </div>
    );
  }
}

Accordion.propTypes = {
  classes: PropTypes.object.isRequired,
  stopAlerts: ImmutablePropTypes.map,
};

Accordion.defaultProps = {
  stopAlerts: new Map(),
};

export default withStyles(styles)(Accordion);